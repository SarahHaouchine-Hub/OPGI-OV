<?php

namespace App\Imports;

use App\Models\Aide;
use App\Models\Logement;
use App\Models\Ov;
use App\Models\Programme;
use App\Models\Souscripteur;
use App\Models\Wilaya;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Import LPA (Logement Promotionnel Aidé)
 *
 * ══════════════════════════════════════════════════════════════════════════════
 * STRUCTURE DES COLONNES (données à partir de la ligne 9, index 0-based)
 * ══════════════════════════════════════════════════════════════════════════════
 *
 *  0  → 16   A→Q    Souscripteur + Logement
 *  17 → 25   R→Z    OV 1  (9 cols : tranche·%·payé·restant·vsp·reçu·agence·nagence·date)
 *  26 → 30   AA→AE  Aide BNH (5 cols : type·montant·convention·décision·date)
 *  31 → 39   AF→AN  OV 2
 *  40 → 42   AO→AQ  Aide FNPOS (3 cols : montant·décision·date)
 *  43         AR     colonne vide (spacer)
 *  44 → 52   AS→BA  OV 3
 *  53 → 61   BB→BJ  OV 4
 *  62 → 70   BK→BS  OV 5
 *
 * ══════════════════════════════════════════════════════════════════════════════
 * LOGIQUE FNPOS — déduction pilotée par la date
 * ══════════════════════════════════════════════════════════════════════════════
 *
 *  - BNH  : toujours déduite AVANT T1 (dès le calcul de la base de T1).
 *  - FNPOS: déduite EN UNE SEULE FOIS du montant restant global, juste avant
 *            la première tranche dont la DATE DE PAIEMENT est
 *            POSTÉRIEURE OU ÉGALE à la date FNPOS.
 *  - Le montant FNPOS utilisé est celui du fichier Excel (montant réel),
 *    pas une constante fixe. Cela peut différer du montant utilisé par
 *    OvController (500 000 DA fixe) pour les saisies manuelles.
 *
 * ══════════════════════════════════════════════════════════════════════════════
 * CHANGEMENTS v6
 * ══════════════════════════════════════════════════════════════════════════════
 *  - Aide type : 'cnl' remplacé par 'bnh' (alignement avec OvController)
 *  - N° convention BNH récupéré depuis la table sites (num_convention_bnh)
 *    si la colonne AC est vide ; la valeur du fichier prime si renseignée.
 *  - VSP : obligatoire OUI pour T2 uniquement. Pour les autres tranches,
 *    le champ est optionnel (false par défaut).
 */
class LpaImport extends BaseImport
{
    private const LPA_TRANCHES = [1 => 20, 2 => 15, 3 => 35, 4 => 25, 5 => 5];

    private const OV_OFFSETS = [
        1 => 17,   // R  — OV1
        2 => 31,   // AF — OV2
        3 => 44,   // AS — OV3
        4 => 53,   // BB — OV4
        5 => 62,   // BK — OV5
    ];

    private const BNH_OFFSET   = 26;  // AA
    private const FNPOS_OFFSET = 40;  // AO

    public function startRow(): int { return 9; }

    // ─────────────────────────────────────────────────────────────────────────
    public function collection(Collection $rows): void
    // ─────────────────────────────────────────────────────────────────────────
    {
        foreach ($rows as $i => $row) {
            $line = $i + 8;

            $arr = array_pad(array_values($row->toArray()), 71, null);

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

                // ── 2. Aide BNH (AA→AE = index 26→30) ───────────────────
                $o           = self::BNH_OFFSET;
                $typeBnh     = strtolower($this->str($arr[$o]     ?? ''));
                $montantBnh  = $this->num($arr[$o + 1] ?? null);
                $numConvBnh  = $this->str($arr[$o + 2] ?? '');
                $numDecBnh   = $this->str($arr[$o + 3] ?? '');
                $dateBnh     = $this->parseDate($arr[$o + 4] ?? '');

                // ── 3. Aide FNPOS (AO→AQ = index 40→42) ──────────────────
                $f            = self::FNPOS_OFFSET;
                $montantFnpos = $this->num($arr[$f]     ?? null);
                $numDecFnpos  = $this->str($arr[$f + 1] ?? '');
                $dateFnposStr = $this->str($arr[$f + 2] ?? '');
                $dateFnpos    = $this->parseDate($dateFnposStr);
                $hasFnpos     = $montantFnpos > 0;

                // ── 4. Blocs OV ───────────────────────────────────────────
                // date paiement = offset + 8 (col Z, AN, BA, BJ, BS)
                $ovBlocs = [];
                foreach (self::OV_OFFSETS as $slot => $offset) {
                    $numTranche = (int)$this->num($arr[$offset] ?? null);
                    if ($numTranche >= 1 && $numTranche <= 5) {
                        $ovBlocs[$numTranche] = [
                            'numTranche'   => $numTranche,
                            'pourcentage'  => $this->num($arr[$offset + 1] ?? null),
                            'montantPaye'  => $this->num($arr[$offset + 2] ?? null),
                            'montantReste' => $this->num($arr[$offset + 3] ?? null),
                            'vspRaw'       => strtoupper($this->str($arr[$offset + 4] ?? '')),
                            'datePaiement' => $this->parseDate($arr[$offset + 8] ?? ''),
                            'paiOffset'    => $offset + 5, // reçu=+5, agence=+6, nagence=+7
                        ];
                    }
                }

                // ── 5. Validations obligatoires ───────────────────────────
                $this->requireAll(compact(
                    'nom','prenom','nom_ar','prenom_ar','date_naissance','nin',
                    'wilayaVal','programmeVal','siteVal','communeVal',
                    'batiment','etage','porte','num_lot','surface','typologie','prix'
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

                // ── 6. Wilaya ─────────────────────────────────────────────
                $wilaya = Wilaya::whereRaw('LOWER(TRIM(nom)) = ?', [strtolower($wilayaVal)])->first();
                if (!$wilaya) throw new \Exception("Wilaya introuvable : «{$wilayaVal}»");

                // ── 7. Programme ──────────────────────────────────────────
                $programme = Programme::where('is_active', 1)
                    ->whereRaw('LOWER(TRIM(libelle)) = ?', [strtolower(trim($programmeVal))])
                    ->first()
                    ?? Programme::where('is_active', 1)
                        ->whereRaw('LOWER(libelle) LIKE ?', ['%'.strtolower(trim($programmeVal)).'%'])
                        ->first();

                if (!$programme) {
                    throw new \Exception("Programme «LPA» introuvable ou inactif en base.");
                }

                // ── 8. Site + Logement ────────────────────────────────────
                $site     = $this->resolveOrCreateSite($wilaya, $programme, $siteVal, $communeVal);
                $logement = $this->resolveOrCreateLogement(
                    $site, $programme, $batiment, $etage, $porte,
                    $num_lot, $surface, $typologie, $prix
                );

                // ── 9. NIN unique ─────────────────────────────────────────
                if (Souscripteur::where('nin', $nin)->exists()) {
                    throw new \Exception("NIN déjà enregistré : «{$nin}»");
                }

                // ── 10. Code + QR souscripteur ────────────────────────────
                $codeLPL = $this->generateCodeLPL($logement);
                [$qrPlain, $qrHashed, $qrCode] = $this->buildQrSous(
                    $nom, $prenom, $programme->libelle, $site->libelle, $codeLPL
                );

                // ── 11. MAJ logement ──────────────────────────────────────
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

                // ── 12. Souscripteur ──────────────────────────────────────
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

                // ── 13. Aide BNH ──────────────────────────────────────────
                // ✅ type 'bnh' en base (accepte aussi 'cnl' pour rétrocompatibilité)
                $hasBnhAide    = false;
                $montantBnhVal = 0.0;

                if ($typeBnh !== '') {
                    if (!in_array($typeBnh, ['bnh', 'cnl'])) {
                        throw new \Exception(
                            "Colonne AA (Type Aide) : valeur «{$typeBnh}» invalide. "
                            . "Valeurs acceptées : 'bnh' (ou 'cnl' pour rétrocompatibilité)."
                        );
                    }
                    if ($montantBnh <= 0) {
                        throw new \Exception("Montant BNH obligatoire si aide BNH renseignée.");
                    }
                    if ($numDecBnh === '') {
                        throw new \Exception("Num. décision obligatoire pour l'aide BNH.");
                    }
                    if ($dateBnh === '') {
                        throw new \Exception("Date obligatoire pour l'aide BNH.");
                    }

                    // ✅ N° convention : priorité colonne AC, fallback table sites
                    $numConvFinal = $numConvBnh !== ''
                        ? $numConvBnh
                        : ($site->num_convention_bnh ?? null);

                    if (!$numConvFinal) {
                        throw new \Exception(
                            "Num. convention BNH manquant : renseignez la colonne AC "
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

                // ── 14. Validation FNPOS (insertion différée au §16) ──────
                if ($hasFnpos) {
                    if ($numDecFnpos === '') {
                        throw new \Exception(
                            "Num. décision FNPOS obligatoire si montant FNPOS renseigné."
                        );
                    }
                    if ($dateFnpos === '') {
                        throw new \Exception(
                            "Date FNPOS obligatoire si montant FNPOS renseigné. "
                            . "La date détermine à partir de quelle tranche elle est déduite."
                        );
                    }
                }

                // ── 15. BNH obligatoire si OVs présents ──────────────────
                if (!empty($ovBlocs) && !$hasBnhAide) {
                    throw new \Exception(
                        "Aide BNH obligatoire avant de générer un OV LPA. "
                        . "Renseignez les colonnes AA→AE (type=bnh) sur cette même ligne."
                    );
                }

                // ── 16. Boucle OV avec déduction FNPOS pilotée par date ───
                ksort($ovBlocs);

                $prixLogement = (float)$logement->prix;

                // Le reste global démarre à Prix - BNH
                $resteGlobal  = $prixLogement - $montantBnhVal;
                $fnposInseree = false;

                foreach ($ovBlocs as $numTranche => $bloc) {

                    // ── Décider si FNPOS s'applique AVANT cette tranche ────
                    if ($hasFnpos && !$fnposInseree) {
                        $datePaiTranche = $bloc['datePaiement'];

                        $fnposDateObj = ($dateFnpos !== '')
                            ? \DateTime::createFromFormat('Y-m-d', $dateFnpos)
                            : null;

                        $paiDateObj = ($datePaiTranche !== '' && $datePaiTranche !== null)
                            ? \DateTime::createFromFormat('Y-m-d', $datePaiTranche)
                            : null;

                        // Appliquer si : pas de date OV, pas de date FNPOS, ou dateFNPOS ≤ dateOV
                        $appliquer = ($paiDateObj === null)
                            || ($fnposDateObj === null)
                            || ($fnposDateObj <= $paiDateObj);

                        if ($appliquer) {
                            Aide::create([
                                'souscripteur_id' => $souscripteur->id,
                                'type'            => 'fnpos',
                                'montant'         => $montantFnpos, // ✅ montant réel du fichier
                                'num_convention'  => null,
                                'num_decision'    => $numDecFnpos,
                                'date'            => $dateFnpos,
                                'pieces_jointes'  => null,
                                'user_id'         => Auth::id(),
                            ]);

                            // ✅ Déduire la FNPOS réelle du reste global
                            $resteGlobal  -= (float)$montantFnpos;
                            $fnposInseree  = true;
                        }
                    }

                    // ── VSP : obligatoire OUI uniquement pour T2 ──────────
                    $vsp = ($bloc['vspRaw'] === 'OUI');
                    if ($numTranche === 2 && !$vsp) {
                        throw new \Exception(
                            "VSP obligatoire (OUI) pour la tranche 2 LPA. "
                            . "Colonne AJ doit contenir 'OUI'."
                        );
                    }

                    // ── Doublon tranche ────────────────────────────────────
                    if (Ov::where('souscripteur_id', $souscripteur->id)
                           ->where('numero_tranche', $numTranche)->exists()) {
                        throw new \Exception(
                            "Tranche {$numTranche} déjà enregistrée pour ce souscripteur."
                        );
                    }

                    // ── Calcul montant ─────────────────────────────────────
                    $pctFixe     = self::LPA_TRANCHES[$numTranche];
                    $pourcentage = $bloc['pourcentage'] > 0 ? $bloc['pourcentage'] : $pctFixe;

                    // ✅ Priorité : valeur saisie dans le fichier Excel,
                    //              sinon calcul automatique (% × prix total)
                    $montantPaye = $bloc['montantPaye'] > 0
                        ? (float)$bloc['montantPaye']
                        : round($prixLogement * $pourcentage / 100, 2);

                    // ✅ Montant restant = reste global après cette tranche
                    $montantReste = max(0.0, $resteGlobal - $montantPaye);

                    // ── QR + OV ────────────────────────────────────────────
                    [$qrOvPlain, $qrOvHashed, $qrOvCode] = $this->buildQr(
                        'LPA', $souscripteur, $montantPaye, $numTranche
                    );

                    $ov = Ov::create([
                        'souscripteur_id'   => $souscripteur->id,
                        'montant_total'     => $prixLogement,
                        'pourcentage'       => $pourcentage,
                        'montant_paye'      => $montantPaye,
                        'montant_restant'   => $montantReste,
                        'numero_tranche'    => $numTranche,
                        'vsp'               => $vsp,
                        'qr_content_plain'  => $qrOvPlain,
                        'qr_content_hashed' => $qrOvHashed,
                        'qrcode'            => $qrOvCode,
                        'user_id'           => Auth::id(),
                    ]);

                    $this->createPaiementIfPresent($arr, $bloc['paiOffset'], $ov->id);

                    // ✅ Déduire le montant payé du reste global
                    $resteGlobal -= $montantPaye;
                }

                // ── 17. FNPOS non déclenchée pendant les OVs ──────────────
                // Insérée quand même pour être disponible pour les prochaines
                // tranches générées manuellement depuis l'interface.
                if ($hasFnpos && !$fnposInseree) {
                    Aide::create([
                        'souscripteur_id' => $souscripteur->id,
                        'type'            => 'fnpos',
                        'montant'         => $montantFnpos, // ✅ montant réel du fichier
                        'num_convention'  => null,
                        'num_decision'    => $numDecFnpos,
                        'date'            => $dateFnpos,
                        'pieces_jointes'  => null,
                        'user_id'         => Auth::id(),
                    ]);
                }

                // ── 18. Flag logement ──────────────────────────────────────
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