<?php

namespace App\Imports;

use App\Models\Ov;
use App\Models\Programme;
use App\Models\Souscripteur;
use App\Models\Wilaya;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Import LSP (Location-Vente Promotionnelle)
 *
 * Tables remplies : souscripteurs · logements · sites · ordres_versement · paiements
 *
 * Structure colonnes (données ligne 8+) :
 *  A→F   Souscripteur (nom, prénom, nom_ar, prénom_ar, date_naiss, nin)
 *  G→Q   Logement (wilaya, programme, site, commune, bat, etage, porte, lot, surface, typo, prix)
 *
 *  ─── OV 1 + Paiement 1 (R→X = index 17→23) ─────────────────────────────────
 *  R   Montant Payé (DA)           — déclenche la création de l'OV si > 0
 *  S   Montant Restant (DA)        — calculé auto si vide
 *  T   N° Tranche                  — calculé auto si vide
 *  U   Num. Reçu                   — optionnel (nullable)
 *  V   Nom Agence                  — obligatoire si paiement renseigné
 *  W   N° Agence                   — optionnel
 *  X   Date Paiement JJ/MM/AAAA   — obligatoire si paiement renseigné
 *
 *  ─── OV 2 + Paiement 2 (Y→AE = index 24→30) ────────────────────────────────
 *  ─── OV 3 + Paiement 3 (AF→AL = index 31→37) ───────────────────────────────
 *  ─── OV 4 + Paiement 4 (AM→AS = index 38→44) ───────────────────────────────
 *  ─── OV 5 + Paiement 5 (AT→AZ = index 45→51) ───────────────────────────────
 *
 *  Chaque bloc OV+Paiement = 7 colonnes identiques à OV 1.
 *  Un bloc est ignoré si Montant Payé est vide ou nul.
 *  Le traitement s'arrête au premier bloc vide (tranches séquentielles).
 */
class LspImport extends BaseImport
{
    private const OV_BLOCK_SIZE  = 7;  // cols par bloc OV+Paiement
    private const OV_START_INDEX = 17; // index 0-based du premier bloc
    private const MAX_OVS        = 5;  // nombre max de blocs

    public function startRow(): int { return 9; }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $i => $row) {
            $line = $i + 8;
            // 17 cols logement + 5 blocs × 7 = 52 colonnes
            $arr = array_pad($row->toArray(), 52, null);

            $filled = array_filter($arr, fn($v) => $v !== null && trim((string)$v) !== '');
            if (count($filled) < 3) continue;

            DB::beginTransaction();
            try {
                // ── 1. Souscripteur + Logement ────────────────────────────
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

                // ── 2. Validation champs obligatoires ─────────────────────
                $this->requireAll(compact(
                    'nom','prenom','nom_ar','prenom_ar','date_naissance','nin',
                    'wilayaVal','programmeVal','siteVal','communeVal',
                    'batiment','etage','porte','num_lot','surface','typologie','prix'
                ));

                if (strtoupper(trim($programmeVal)) !== 'LSP') {
                    throw new \Exception(
                        "Programme invalide : «{$programmeVal}». Ce fichier accepte uniquement «LSP»."
                    );
                }
                if (!is_numeric($surface)) throw new \Exception("Surface invalide : «{$surface}»");
                if (!is_numeric($prix) || (float)$prix < 0) throw new \Exception("Prix invalide : «{$prix}»");

                // ── 3. Wilaya ─────────────────────────────────────────────
                $wilaya = Wilaya::whereRaw('LOWER(TRIM(nom)) = ?', [strtolower($wilayaVal)])->first();
                if (!$wilaya) throw new \Exception("Wilaya introuvable : «{$wilayaVal}»");

                // ── 4. Programme ──────────────────────────────────────────
                $programme = Programme::where('is_active', 1)
                    ->whereRaw('LOWER(TRIM(libelle)) = ?', [strtolower(trim($programmeVal))])
                    ->first()
                    ?? Programme::where('is_active', 1)
                        ->whereRaw('LOWER(libelle) LIKE ?', ['%'.strtolower(trim($programmeVal)).'%'])
                        ->first();

                if (!$programme) throw new \Exception("Programme «LSP» introuvable ou inactif en base.");

                // ── 5. Site + Logement ────────────────────────────────────
                $site     = $this->resolveOrCreateSite($wilaya, $programme, $siteVal, $communeVal);
                $logement = $this->resolveOrCreateLogement(
                    $site, $programme, $batiment, $etage, $porte,
                    $num_lot, $surface, $typologie, $prix
                );

                // ── 6. NIN unique ─────────────────────────────────────────
                if (Souscripteur::where('nin', $nin)->exists()) {
                    throw new \Exception("NIN déjà enregistré : «{$nin}»");
                }

                // ── 7. Code + QR souscripteur ─────────────────────────────
                $codeLPL = $this->generateCodeLPL($logement);
                [$qrPlain, $qrHashed, $qrCode] = $this->buildQrSous(
                    $nom, $prenom, $programme->libelle, $site->libelle, $codeLPL
                );

                // ── 8. MAJ logement (flag → 1 = Attribué) ────────────────
                $logement->update([
                    'code_loge_lpl' => $codeLPL, 'flag' => 1,
                    'num_lot'       => $num_lot,
                    'surface'       => (float)$surface,
                    'typologie'     => $typologie,
                    'prix'          => (float)$prix,
                    'programme_id'  => $programme->id,
                    'user_id'       => Auth::id(),
                ]);

                // ── 9. Souscripteur ───────────────────────────────────────
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

                // ── 10. Boucle OVs + Paiements ───────────────────────────
                $prixLogement  = (float)$logement->prix;
                $totalPayeLocal = 0.0; // cumul des montants payés dans cette transaction
                $anyOvCreated  = false;

                for ($ovIdx = 0; $ovIdx < self::MAX_OVS; $ovIdx++) {
                    $base = self::OV_START_INDEX + ($ovIdx * self::OV_BLOCK_SIZE);

                    $montantPaye  = $this->num($arr[$base]      ?? null); // col +0
                    $montantReste = $this->num($arr[$base + 1]  ?? null); // col +1
                    $numTranche   = (int)$this->num($arr[$base + 2] ?? null); // col +2
                    // cols +3 à +6 : paiement (traité via createPaiementIfPresent)

                    // Bloc vide → arrêt de la boucle
                    if ($montantPaye <= 0) break;

                    if ($montantPaye > $prixLogement) {
                        throw new \Exception(
                            "OV ".($ovIdx+1)." — Montant payé ({$montantPaye}) supérieur au prix logement ({$prixLogement})."
                        );
                    }

                    // Calcul montant restant si absent
                    if ($montantReste <= 0) {
                        $montantReste = max(0, $prixLogement - $totalPayeLocal - $montantPaye);
                    }

                    // N° tranche auto si absent
                    if ($numTranche <= 0) {
                        $numTranche = $ovIdx + 1;
                    }

                    $pourcentage = round(($montantPaye / max(1, $prixLogement)) * 100, 2);

                    [$qrOvPlain, $qrOvHashed, $qrOvCode] = $this->buildQr(
                        'LSP', $souscripteur, $montantPaye, $numTranche
                    );

                    $ov = Ov::create([
                        'souscripteur_id'  => $souscripteur->id,
                        'montant_total'     => $prixLogement,
                        'pourcentage'       => $pourcentage,
                        'montant_paye'      => $montantPaye,
                        'montant_restant'   => $montantReste,
                        'numero_tranche'    => $numTranche,
                        'vsp'               => false,
                        'qr_content_plain'  => $qrOvPlain,
                        'qr_content_hashed' => $qrOvHashed,
                        'qrcode'            => $qrOvCode,
                        'user_id'           => Auth::id(),
                    ]);

                    // Paiement (optionnel) — colonnes +3 à +6 du bloc
                    $this->createPaiementIfPresent($arr, $base + 3, $ov->id);

                    $totalPayeLocal += $montantPaye;
                    $anyOvCreated    = true;
                }

                // Flag logement → 2 (en cours de paiement) si au moins 1 OV créé
                if ($anyOvCreated) {
                    $logement->update(['flag' => 2]);
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