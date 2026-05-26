<?php

namespace App\Imports;

use App\Models\Aide;
use App\Models\Ov;
use App\Models\Paiement;
use App\Models\Programme;
use App\Models\Souscripteur;
use App\Models\Wilaya;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Import LPA (Logement Promotionnel Aidé)
 *
 * ══════════════════════════════════════════════════════════════════════════════
 * STRUCTURE DES COLONNES — TEMPLATE_INIT_v2 (index 0-based, ligne 5+)
 * ══════════════════════════════════════════════════════════════════════════════
 *
 *  0  → 3    A→D    Souscripteur — identité (nom, prénom, date_naiss, NIN)
 *  4  → 9    E→J    Souscripteur — état civil (situation, lieu_naiss, nom_père,
 *                                               prénom_père, nom_mère, prénom_mère)
 *  10 → 18   K→S    Conjoint (nom, prénom, NIN, date_naiss, lieu_naiss,
 *                             nom_père, prénom_père, nom_mère, prénom_mère)
 *  19          T    VSP souscripteur (OUI / NON)
 *  20 → 30   U→AE   Logement (wilaya, programme, site, commune, bât, étage,
 *                             n°log, n°lot, surface, typologie, prix)
 *  31 → 34   AF→AI  Aide BNH (montant, convention, décision, date)
 *  35 → 37   AJ→AL  OV 1 (montant_payé, num_reçu, date_reçu)
 *  38 → 40   AM→AO  OV 2
 *  41 → 43   AP→AR  OV 3
 *  44 → 46   AS→AU  OV 4
 *  47 → 49   AV→AX  OV 5
 *  50 → 53   AY→BB  Agence (nom=50, adresse=51, n°agence=52, n°compte=53)
 *  54          BC   Aide FNPOS — Num. Décision
 *  55          BD   Aide FNPOS — Date
 *
 * ══════════════════════════════════════════════════════════════════════════════
 * CHANGELOG
 * ══════════════════════════════════════════════════════════════════════════════
 *  v13 — Lecture de adresse_agence (col 51) et num_compte_agence (col 53),
 *        transmis à resolveOrCreateSite() et Paiement::create().
 *  v12 — FIX FINAL : contournement du cast boolean par INSERT direct via DB::table()
 *        pour garantir que vsp=1 est bien écrit en base (tinyint(1)).
 *  v11 — VSP (OUI→1 / NON→0), num_convention_bnh, nom_agence, num_agence transmis.
 *  v10 — Correction FNPOS_OFFSET : 53→54.
 */
class LpaImport extends BaseImport
{
    private const LPA_TRANCHES  = [1 => 20, 2 => 15, 3 => 35, 4 => 25, 5 => 5];
    private const FNPOS_MONTANT = 500000;

    private const OV_OFFSETS = [
        1 => 35,
        2 => 38,
        3 => 41,
        4 => 44,
        5 => 47,
    ];

    private const AIDE_OFFSET   = 31;
    private const AGENCE_OFFSET = 50;
    private const FNPOS_OFFSET  = 55;
    private const ROW_SIZE      = 57;

    private const SITUATION_MAP = [
        'mariée'      => 'Marié',
        'marié'       => 'Marié',
        'celibataire' => 'Célibataire',
        'célibataire' => 'Célibataire',
        'divorcée'    => 'Divorcé',
        'divorcé'     => 'Divorcé',
        'veuve'       => 'Veuf',
        'veuf'        => 'Veuf',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    public function startRow(): int { return 5; }

    // ─────────────────────────────────────────────────────────────────────────
    protected function safeParse(mixed $value): string
    {
        if ($value === null || trim((string)$value) === '') {
            return '';
        }
        $result = $this->parseDate($value);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$result)) {
            return (string)$result;
        }
        return '';
    }

    // ─────────────────────────────────────────────────────────────────────────
    private function normalizeSituation(string $raw): string
    {
        if ($raw === '') return '';
        return self::SITUATION_MAP[strtolower(trim($raw))] ?? ucfirst(trim($raw));
    }

    // ─────────────────────────────────────────────────────────────────────────
    public function collection(Collection $rows): void
    {
        foreach ($rows as $i => $row) {
            $line = $i + $this->startRow();
            $arr  = array_pad(array_values($row->toArray()), self::ROW_SIZE, null);

            $filled = array_filter($arr, fn($v) => $v !== null && trim((string)$v) !== '');
            if (count($filled) < 3) continue;

            DB::beginTransaction();
            try {

                // ── Souscripteur identité (A→D = 0→3) ─────────────────────
                $nom            = $this->str($arr[0]);
                $prenom         = $this->str($arr[1]);
                $date_naissance = $this->safeParse($arr[2] ?? '');
                $nin            = $this->parseNin($arr[3] ?? '');

                // ── Souscripteur état civil (E→J = 4→9) ───────────────────
                $situationFam  = $this->normalizeSituation($this->str($arr[4] ?? ''));
                $lieuNaissance = $this->str($arr[5] ?? '');
                $nomPere       = $this->str($arr[6] ?? '');
                $prenomPere    = $this->str($arr[7] ?? '');
                $nomMere       = $this->str($arr[8] ?? '');
                $prenomMere    = $this->str($arr[9] ?? '');

                // ── Conjoint (K→S = 10→18) ────────────────────────────────
                $conjointNom        = $this->str($arr[10] ?? '');
                $conjointPrenom     = $this->str($arr[11] ?? '');
                $conjointNin        = $this->str($arr[12] ?? '');
                $conjointDateNaiss  = $this->safeParse($arr[13] ?? '');
                $conjointLieuNaiss  = $this->str($arr[14] ?? '');
                $conjointNomPere    = $this->str($arr[15] ?? '');
                $conjointPrenomPere = $this->str($arr[16] ?? '');
                $conjointNomMere    = $this->str($arr[17] ?? '');
                $conjointPrenomMere = $this->str($arr[18] ?? '');

                // ── VSP (T = 19) ───────────────────────────────────────────
                $vspCell = $arr[19] ?? null;
                $vspStr  = strtoupper(trim(preg_replace('/\s+/u', '', (string)$vspCell)));
                $vsp     = in_array($vspStr, ['OUI', 'O', 'YES', 'Y', '1'], true) ? 1 : 0;

                // ── Logement (U→AE = 20→30) ───────────────────────────────
                $wilayaVal    = $this->str($arr[20]);
                $programmeVal = $this->str($arr[21]);
                $siteVal      = $this->str($arr[22]);
                $communeVal   = $this->str($arr[23]);
                $batiment     = $this->str($arr[24]);
                $etage        = $this->str($arr[25]);
                $porte        = $this->str($arr[26]);
                $num_lot      = $this->str($arr[27]);
                $surface      = $this->str($arr[28]);
                $typologie    = $this->str($arr[29]);
                $prix         = $this->str($arr[30] ?? '');

                // ── Aide BNH (AF→AI = 31→34) ──────────────────────────────
                $o          = self::AIDE_OFFSET;
                $montantBnh = $this->num($arr[$o]     ?? null);
                $numConvBnh = $this->str($arr[$o + 1] ?? '');
                $numDecBnh  = $this->str($arr[$o + 2] ?? '');
                $dateBnh    = $this->safeParse($arr[$o + 3] ?? '');

               // ── Agence (AY→BC = 50→54) ────────────────────────────────
                $a                = self::AGENCE_OFFSET;
                $nomAgence        = $this->str($arr[$a]     ?? ''); // col 50 — nom
                $adresseAgence    = $this->str($arr[$a + 1] ?? ''); // col 51 — adresse  ← NOUVEAU
                $numAgence        = $this->str($arr[$a + 2] ?? ''); // col 52 — n°agence
                $numCompteAgence  = $this->str($arr[$a + 3] ?? ''); // col 53 — n°compte ← NOUVEAU
                $titulaire        = $this->str($arr[$a + 4] ?? ''); // col 54 ← NOUVEAU

                // ── Aide FNPOS (BD→BE = 55→56) ────────────────────────────
                $f            = self::FNPOS_OFFSET;
                $numDecFnpos  = $this->str($arr[$f]     ?? '');
                $dateFnposRaw = $this->str($arr[$f + 1] ?? '');
                $dateFnpos    = $this->safeParse($dateFnposRaw);
                $hasFnpos     = ($numDecFnpos !== '' || $dateFnposRaw !== '');

                // ── Blocs OV ──────────────────────────────────────────────
                $ovBlocs = [];
                foreach (self::OV_OFFSETS as $numTranche => $offset) {
                    $montantPaye = $this->num($arr[$offset] ?? null);
                    if ($montantPaye > 0) {
                        $ovBlocs[$numTranche] = [
                            'numTranche'   => $numTranche,
                            'montantPaye'  => $montantPaye,
                            'numRecu'      => $this->str($arr[$offset + 1] ?? ''),
                            'datePaiement' => $this->safeParse($arr[$offset + 2] ?? ''),
                        ];
                    }
                }

                // ── Validations ───────────────────────────────────────────
                $this->requireAll(compact(
                    'nom', 'prenom', 'date_naissance', 'nin',
                    'wilayaVal', 'programmeVal', 'siteVal', 'communeVal',
                    'batiment', 'etage', 'porte', 'num_lot',
                    'surface', 'typologie', 'prix'
                ));

                if (strtoupper(trim($programmeVal)) !== 'LPA') {
                    throw new \Exception(
                        "Programme invalide : «{$programmeVal}». Ce fichier accepte uniquement «LPA»."
                    );
                }
                if (!is_numeric($surface)) {
                    throw new \Exception("Surface invalide : «{$surface}»");
                }
                if (!is_numeric($prix) || (float)$prix < 0) {
                    throw new \Exception("Prix invalide : «{$prix}»");
                }

                // ── Résolution des entités ────────────────────────────────
                $wilaya = Wilaya::whereRaw('LOWER(TRIM(nom)) = ?', [strtolower($wilayaVal)])->first();
                if (!$wilaya) throw new \Exception("Wilaya introuvable : «{$wilayaVal}»");

                $programme = Programme::where('is_active', 1)
                    ->whereRaw('LOWER(TRIM(libelle)) = ?', [strtolower(trim($programmeVal))])
                    ->first()
                    ?? Programme::where('is_active', 1)
                        ->whereRaw('LOWER(libelle) LIKE ?', ['%'.strtolower(trim($programmeVal)).'%'])
                        ->first();

                if (!$programme) {
                    throw new \Exception("Programme «LPA» introuvable ou inactif en base.");
                }

                $site = $this->resolveOrCreateSite(
                    $wilaya, $programme, $siteVal, $communeVal,
                    $numConvBnh, $nomAgence, $numAgence,
                    $adresseAgence, $numCompteAgence ,   $titulaire // ← NOUVEAU
                );

                $logement = $this->resolveOrCreateLogement(
                    $site, $programme, $batiment, $etage, $porte,
                    $num_lot, $surface, $typologie, $prix
                );

                if (Souscripteur::where('nin', $nin)->exists()) {
                    throw new \Exception("NIN déjà enregistré : «{$nin}»");
                }

                // ── Création souscripteur ─────────────────────────────────
                $codeLPL = $this->generateCodeLPL($logement);
                [$qrPlain, $qrHashed, $qrCode] = $this->buildQrSous(
                    $nom, $prenom, $programme->libelle, $site->libelle, $codeLPL
                );

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

                $souscripteur = Souscripteur::create([
                    'nom'                      => $nom,
                    'prenom'                   => $prenom,
                    'date_naissance'           => $date_naissance,
                    'nin'                      => $nin,
                    'situation_familiale'      => $situationFam   ?: null,
                    'lieu_naissance'           => $lieuNaissance  ?: null,
                    'nom_pere'                 => $nomPere        ?: null,
                    'prenom_pere'              => $prenomPere     ?: null,
                    'nom_mere'                 => $nomMere        ?: null,
                    'prenom_mere'              => $prenomMere     ?: null,
                    'conjoint_nom'             => $conjointNom        ?: null,
                    'conjoint_prenom'          => $conjointPrenom     ?: null,
                    'conjoint_nin'             => $conjointNin        ?: null,
                    'conjoint_date_naissance'  => $conjointDateNaiss  ?: null,
                    'conjoint_lieu_naissance'  => $conjointLieuNaiss  ?: null,
                    'conjoint_nom_pere'        => $conjointNomPere    ?: null,
                    'conjoint_prenom_pere'     => $conjointPrenomPere ?: null,
                    'conjoint_nom_mere'        => $conjointNomMere    ?: null,
                    'conjoint_prenom_mere'     => $conjointPrenomMere ?: null,
                    'code_loge_lpl'            => $codeLPL,
                    'qr_content_plain'         => $qrPlain,
                    'qr_content_hashed'        => $qrHashed,
                    'qrcode'                   => $qrCode,
                    'user_id'                  => Auth::id(),
                ]);

                // ── Aide BNH ──────────────────────────────────────────────
                $hasBnhAide    = false;
                $montantBnhVal = 0.0;

                if ($montantBnh > 0) {
                    if ($numDecBnh === '') {
                        throw new \Exception("Num. décision BNH (col AH) obligatoire.");
                    }
                    if ($dateBnh === '') {
                        throw new \Exception("Date BNH (col AI) obligatoire.");
                    }

                    $numConvFinal = $numConvBnh !== ''
                        ? $numConvBnh
                        : ($site->num_convention_bnh ?? null);

                    if (!$numConvFinal) {
                        throw new \Exception(
                            "Num. convention BNH manquant : renseignez la colonne AG "
                            . "ou configurez num_convention_bnh dans les paramètres du site."
                        );
                    }

                    Aide::create([
                        'souscripteur_id' => $souscripteur->id,
                        'type'            => 'bnh',
                        'montant'         => $montantBnh,
                        'num_convention'  => $numConvFinal,
                        'num_decision'    => $numDecBnh,
                        'date'            => $dateBnh,
                        'pieces_jointes'  => null,
                        'user_id'         => Auth::id(),
                    ]);

                    $hasBnhAide    = true;
                    $montantBnhVal = (float)$montantBnh;
                }

                // ── Validation FNPOS ──────────────────────────────────────
                if ($hasFnpos) {
                    if ($numDecFnpos === '') {
                        throw new \Exception(
                            "Num. décision FNPOS (col BC) obligatoire si FNPOS renseigné."
                        );
                    }
                    if ($dateFnpos === '') {
                        throw new \Exception(
                            "Date FNPOS (col BD) obligatoire si FNPOS renseigné."
                        );
                    }
                }

                if (!empty($ovBlocs) && !$hasBnhAide) {
                    throw new \Exception(
                        "Aide BNH obligatoire avant de générer un OV LPA. "
                        . "Renseignez les colonnes AF→AI sur cette même ligne."
                    );
                }

                // ── Boucle OV ─────────────────────────────────────────────
                ksort($ovBlocs);

                $prixLogement = (float)$logement->prix;
                $resteGlobal  = $prixLogement - $montantBnhVal;
                $fnposInseree = false;

                foreach ($ovBlocs as $numTranche => $bloc) {

                    // Déduction FNPOS avant cette tranche si applicable
                    if ($hasFnpos && !$fnposInseree) {
                        $datePaiTranche = $bloc['datePaiement'];

                        $fnposDateObj = ($dateFnpos !== '')
                            ? \DateTime::createFromFormat('Y-m-d', $dateFnpos)
                            : null;

                        $paiDateObj = ($datePaiTranche !== '' && $datePaiTranche !== null)
                            ? \DateTime::createFromFormat('Y-m-d', $datePaiTranche)
                            : null;

                        $appliquer = ($paiDateObj === null)
                            || ($fnposDateObj === null)
                            || ($fnposDateObj <= $paiDateObj);

                        if ($appliquer) {
                            Aide::create([
                                'souscripteur_id' => $souscripteur->id,
                                'type'            => 'fnpos',
                                'montant'         => self::FNPOS_MONTANT,
                                'num_convention'  => null,
                                'num_decision'    => $numDecFnpos,
                                'date'            => $dateFnpos,
                                'pieces_jointes'  => null,
                                'user_id'         => Auth::id(),
                            ]);
                            $resteGlobal  -= self::FNPOS_MONTANT;
                            $fnposInseree  = true;
                        }
                    }

                    if (Ov::where('souscripteur_id', $souscripteur->id)
                            ->where('numero_tranche', $numTranche)->exists()) {
                        throw new \Exception(
                            "Tranche {$numTranche} déjà enregistrée pour ce souscripteur."
                        );
                    }

                    $pctFixe      = self::LPA_TRANCHES[$numTranche];
                    $montantPaye  = (float)$bloc['montantPaye'];
                    $montantReste = max(0.0, $resteGlobal - $montantPaye);

                    [$qrOvPlain, $qrOvHashed, $qrOvCode] = $this->buildQr(
                        'LPA', $souscripteur, $montantPaye, $numTranche
                    );

                    $ovId = DB::table('ordres_versement')->insertGetId([
                        'souscripteur_id'   => $souscripteur->id,
                        'montant_total'     => $prixLogement,
                        'pourcentage'       => $pctFixe,
                        'montant_paye'      => $montantPaye,
                        'montant_restant'   => $montantReste,
                        'numero_tranche'    => $numTranche,
                        'vsp'               => ($vsp && !Ov::where('souscripteur_id', $souscripteur->id)->where('vsp', 1)->exists()) ? 1 : 0,
                        'type_ov'           => null,
                        'qr_content_plain'  => $qrOvPlain,
                        'qr_content_hashed' => $qrOvHashed,
                        'qrcode'            => $qrOvCode,
                        'user_id'           => Auth::id(),
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);

                    $ov = Ov::find($ovId);

                    $datePaiement = $bloc['datePaiement'];
                    $numRecu      = $bloc['numRecu'];

                    if ($datePaiement !== '' && $datePaiement !== null) {
                        Paiement::create([
                            'ov_id'              => $ov->id,
                            'num_recu'           => $numRecu          ?: null,
                            'nom_agence'         => $nomAgence        ?: null,
                            'num_agence'         => $numAgence        ?: null,
                            'adresse_agence'     => $adresseAgence    ?: null, // ← NOUVEAU
                            'num_compte_agence'  => $numCompteAgence  ?: null, // ← NOUVEAU
                            'date_paiement'      => $datePaiement,
                            'recu_pdf'           => null,
                            'user_id'            => Auth::id(),
                        ]);
                    }

                    $resteGlobal -= $montantPaye;
                }

                // ── FNPOS non déclenchée (aucun OV ou dates ultérieures) ──
                if ($hasFnpos && !$fnposInseree) {
                    Aide::create([
                        'souscripteur_id' => $souscripteur->id,
                        'type'            => 'fnpos',
                        'montant'         => self::FNPOS_MONTANT,
                        'num_convention'  => null,
                        'num_decision'    => $numDecFnpos,
                        'date'            => $dateFnpos,
                        'pieces_jointes'  => null,
                        'user_id'         => Auth::id(),
                    ]);
                }

                if (!empty($ovBlocs)) {
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