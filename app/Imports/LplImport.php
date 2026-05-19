<?php

namespace App\Imports;

use App\Models\Logement;
use App\Models\Ov;
use App\Models\Programme;
use App\Models\Souscripteur;
use App\Models\Wilaya;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Import LPL Promotionnel
 *
 * Tables remplies : souscripteurs · logements · sites · ordres_versement · paiements
 *
 * Structure colonnes (données ligne 8+) :
 *  A→F  Souscripteur (nom, prénom, nom_ar, prénom_ar, date_naiss, nin)
 *  G→Q  Logement (wilaya, programme, site, commune, bat, etage, porte, lot, surface, typo, prix)
 *  R    Pourcentage OV (%)           — optionnel
 *  S    Montant Payé (DA)            — optionnel, calculé si vide
 *  T    Montant Restant (DA)         — optionnel, calculé si vide
 *  U    Num. Reçu                    — optionnel (nullable)
 *  V    Nom Agence                   — obligatoire si paiement renseigné
 *  W    N° Agence                    — optionnel
 *  X    Date Paiement (JJ/MM/AAAA)  — obligatoire si paiement renseigné
 */
class LplImport extends BaseImport
{
    public function startRow(): int { return 9; }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $i => $row) {
            $line = $i + 8;
            $arr  = array_pad($row->toArray(), 24, null);

            // Ignorer lignes quasi-vides
            $filled = array_filter($arr, fn($v) => $v !== null && trim((string)$v) !== '');
            if (count($filled) < 3) continue;

            DB::beginTransaction();
            try {
                // ── 1. Lecture colonnes souscripteur + logement ───────────
                $nom            = $this->str($arr[0]);
                $prenom         = $this->str($arr[1]);
                $nom_ar         = $this->str($arr[2]);
                $prenom_ar      = $this->str($arr[3]);
                $date_naissance = $this->parseDate($arr[4] ?? '');
                $nin            = $this->parseNin($arr[5] ?? '');
                $wilayaVal      = $this->str($arr[6]);
                $programmeVal   = $this->str($arr[7]);
                $siteVal        = $this->str($arr[8]);
                $communeVal     = $this->str($arr[9]);
                $batiment       = $this->str($arr[10]);
                $etage          = $this->str($arr[11]);
                $porte          = $this->str($arr[12]);
                $num_lot        = $this->str($arr[13]);
                $surface        = $this->str($arr[14]);
                $typologie      = $this->str($arr[15]);
                $prix           = $this->str($arr[16] ?? '');

                // ── 2. Lecture colonnes OV ────────────────────────────────
                $pourcentage  = $this->num($arr[17] ?? null);
                $montantPaye  = $this->num($arr[18] ?? null);
                $montantReste = $this->num($arr[19] ?? null);

                // ── 3. Validation champs obligatoires ─────────────────────
                $this->requireAll(compact(
                    'nom','prenom','nom_ar','prenom_ar','date_naissance','nin',
                    'wilayaVal','programmeVal','siteVal','communeVal',
                    'batiment','etage','porte','num_lot','surface','typologie','prix'
                ));

                // Vérification programme
                if (strtoupper(trim($programmeVal)) !== 'PROMOTIONNEL') {
                    throw new \Exception(
                        "Programme invalide : «{$programmeVal}». Ce fichier accepte uniquement «LPL Promotionnel»."
                    );
                }
                if (!is_numeric($surface)) throw new \Exception("Surface invalide : «{$surface}»");
                if (!is_numeric($prix) || (float)$prix < 0) throw new \Exception("Prix invalide : «{$prix}»");

                // ── 4. Résolution Wilaya ──────────────────────────────────
                $wilaya = Wilaya::whereRaw('LOWER(TRIM(nom)) = ?', [strtolower($wilayaVal)])->first();
                if (!$wilaya) {
                    $dispo = Wilaya::orderBy('nom')->pluck('nom')->implode(', ');
                    throw new \Exception("Wilaya introuvable : «{$wilayaVal}». Disponibles : {$dispo}");
                }

                // ── 5. Résolution Programme ───────────────────────────────
                $programme = Programme::where('is_active', 1)
                    ->whereRaw('LOWER(TRIM(libelle)) = ?', [strtolower(trim($programmeVal))])
                    ->first()
                    ?? Programme::where('is_active', 1)
                        ->whereRaw('LOWER(libelle) LIKE ?', ['%'.strtolower(trim($programmeVal)).'%'])
                        ->first();

                if (!$programme) throw new \Exception("Programme «LPL Promotionnel» introuvable ou inactif en base.");

                // ── 6. Site ───────────────────────────────────────────────
                $site = $this->resolveOrCreateSite($wilaya, $programme, $siteVal, $communeVal);

                // ── 7. Logement ───────────────────────────────────────────
                $logement = $this->resolveOrCreateLogement(
                    $site, $programme, $batiment, $etage, $porte,
                    $num_lot, $surface, $typologie, $prix
                );

                // ── 8. NIN unique ─────────────────────────────────────────
                if (Souscripteur::where('nin', $nin)->exists()) {
                    throw new \Exception("NIN déjà enregistré : «{$nin}»");
                }

                // ── 9. Code LPL + QR souscripteur ────────────────────────
                $codeLPL = $this->generateCodeLPL($logement);
                [$qrPlain, $qrHashed, $qrCode] = $this->buildQrSous(
                    $nom, $prenom, $programme->libelle, $site->libelle, $codeLPL
                );

                // ── 10. MAJ logement (flag → 1) ───────────────────────────
                $logement->update([
                    'code_loge_lpl' => $codeLPL,
                    'flag'          => 1,
                    'num_lot'       => $num_lot,
                    'surface'       => (float)$surface,
                    'typologie'     => $typologie,
                    'prix'          => (float)$prix,
                    'programme_id'  => $programme->id,
                    'user_id'       => Auth::id(),
                ]);

                // ── 11. Souscripteur ──────────────────────────────────────
                $souscripteur = Souscripteur::create([
                    'nom'               => $nom,
                    'prenom'            => $prenom,
                    'nom_arabe'         => $nom_ar,
                    'prenom_arabe'      => $prenom_ar,
                    'date_naissance'    => $date_naissance,
                    'nin'               => $nin,
                    'code_loge_lpl'     => $codeLPL,
                    'qr_content_plain'  => $qrPlain,
                    'qr_content_hashed' => $qrHashed,
                    'qrcode'            => $qrCode,
                    'user_id'           => Auth::id(),
                ]);

                // ── 12. OV (optionnel) ────────────────────────────────────
                $ov = null;
                if ($pourcentage > 0) {
                    if ($pourcentage > 100) {
                        throw new \Exception("Pourcentage OV invalide : «{$pourcentage}» (max 100).");
                    }

                    $prixLogement = (float)$logement->prix;

                    // Calculs auto si non renseignés
                    if ($montantPaye <= 0) {
                        $montantPaye = round($prixLogement * $pourcentage / 100, 2);
                    }
                    if ($montantReste <= 0) {
                        $montantReste = max(0, $prixLogement - $montantPaye);
                    }

                    [$qrOvPlain, $qrOvHashed, $qrOvCode] = $this->buildQr(
                        'LPL', $souscripteur, $montantPaye
                    );

                    $ov = Ov::create([
                        'souscripteur_id'   => $souscripteur->id,
                        'montant_total'      => $prixLogement,
                        'pourcentage'        => $pourcentage,
                        'montant_paye'       => $montantPaye,
                        'montant_restant'    => $montantReste,
                        'numero_tranche'     => null,
                        'vsp'                => false,
                        'qr_content_plain'   => $qrOvPlain,
                        'qr_content_hashed'  => $qrOvHashed,
                        'qrcode'             => $qrOvCode,
                        'user_id'            => Auth::id(),
                    ]);

                    // Flag logement → 2 (en cours de paiement)
                    $logement->update(['flag' => 2]);
                }

                // ── 13. Paiement (optionnel, uniquement si OV créé) ───────
                // Colonnes : U=index 20, V=21, W=22, X=23
                if ($ov !== null) {
                    $this->createPaiementIfPresent($arr, 20, $ov->id);
                }

                DB::commit();
                $this->imported++;

            } catch (\Exception $e) {
                DB::rollBack();
                $this->errors[] = "Ligne {$line} : " . $e->getMessage();
            }
        }
    }
}