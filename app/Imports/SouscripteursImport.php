<?php

namespace App\Imports;

use App\Models\Logement;
use App\Models\Programme;
use App\Models\Site;
use App\Models\Souscripteur;
use App\Models\Wilaya;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Import Excel → sites + logements + souscripteurs
 *
 * Structure du fichier (15 colonnes, données à partir de la ligne 6) :
 *  A  nom          B  prenom       C  nom_ar        D  prenom_ar
 *  E  date_naiss   F  nin (texte)  G  wilaya        H  programme
 *  I  site         J  commune      K  num_bat       L  num_etage
 *  M  num_porte    N  num_lot      O  surface       P  typologie    Q  prix
 */
class SouscripteursImport implements ToCollection, WithStartRow
{
    public array $errors   = [];
    public int   $imported = 0;

    // ─── Labels lisibles pour le flag logement ───────────────────────────────
    private const FLAG_LABELS = [
        0 => 'Libre',
        1 => 'Attribué',
        2 => 'Réservé',
        3 => 'Désisté (disponible)',
    ];

    // ─── Démarrer la lecture à la ligne 6 (skip titre + 4 lignes d'en-tête) ────
    public function startRow(): int
    {
        return 6;
    }

    // ────────────────────────────────────────────────────────────────────────
    public function collection(Collection $rows): void
    {
        foreach ($rows as $i => $row) {
            // WithStartRow s'occupe déjà de sauter les en-têtes
            // $i = 0 correspond à la ligne Excel 6 (première ligne de données)
            $line = $i + 6; // numéro de ligne Excel réel pour les messages d'erreur
            $arr  = $row->toArray();

            // Garantir au minimum 17 éléments pour éviter "Undefined array key"
            $arr = array_pad($arr, 17, null);

            // Ignorer les lignes entièrement vides ou avec moins de 2 colonnes remplies
            $nonEmpty = array_filter($arr, fn($v) => $v !== null && trim((string)$v) !== '');
            if (count($nonEmpty) < 3) {
                continue;
            }

            DB::beginTransaction();

            try {
                // ══════════════════════════════════════════════════════════════
                //  1. LECTURE & NETTOYAGE DES COLONNES
                // ══════════════════════════════════════════════════════════════
                $nom          = $this->str($arr[0]);
                $prenom       = $this->str($arr[1]);
                $nom_ar       = $this->str($arr[2]);
                $prenom_ar    = $this->str($arr[3]);
                $date_naissance = $this->parseDate($arr[4] ?? '');
                $nin          = $this->parseNin($arr[5] ?? '');
                $wilayaVal    = $this->str($arr[6]);
                $programmeVal = $this->str($arr[7]);
                $siteVal      = $this->str($arr[8]);
                $communeVal   = $this->str($arr[9]);
                $batiment     = $this->str($arr[10]);
                $etage        = $this->str($arr[11]);
                $porte        = $this->str($arr[12]);
                $num_lot      = $this->str($arr[13]);
                $surface      = $this->str($arr[14]);
                $typologie    = $this->str($arr[15]);
                $prix         = $this->str($arr[16] ?? '');

                // ══════════════════════════════════════════════════════════════
                //  2. VALIDATION BASIQUE
                // ══════════════════════════════════════════════════════════════
                $this->requireAll(compact(
                    'nom','prenom','nom_ar','prenom_ar',
                    'date_naissance','nin',
                    'wilayaVal','programmeVal','siteVal','communeVal',
                    'batiment','etage','porte',
                    'num_lot','surface','typologie','prix'
                ));

                if (!is_numeric($surface)) {
                    throw new \Exception("Surface invalide : «{$surface}» (doit être un nombre)");
                }

                if (!is_numeric($prix) || (float)$prix < 0) {
                    throw new \Exception("Prix invalide : «{$prix}» (doit être un nombre positif)");
                }

                // ══════════════════════════════════════════════════════════════
                //  3. RÉSOLUTION WILAYA
                // ══════════════════════════════════════════════════════════════
                $wilaya = Wilaya::whereRaw('LOWER(TRIM(nom)) = ?', [strtolower($wilayaVal)])->first();
                if (!$wilaya) {
                    $available = Wilaya::orderBy('nom')->pluck('nom')->implode(', ');
                    throw new \Exception(
                        "Wilaya introuvable : «{$wilayaVal}». Valeurs acceptées : {$available}"
                    );
                }

                // ══════════════════════════════════════════════════════════════
                //  4. RÉSOLUTION PROGRAMME
                // ══════════════════════════════════════════════════════════════
                $programme = Programme::where('is_active', 1)
                    ->whereRaw('LOWER(TRIM(libelle)) = ?', [strtolower(trim($programmeVal))])
                    ->first();

                // Fallback : correspondance partielle (ex: "LPL" trouve "LPL Promotionnel")
                if (!$programme) {
                    $programme = Programme::where('is_active', 1)
                        ->whereRaw('LOWER(libelle) LIKE ?', ['%' . strtolower(trim($programmeVal)) . '%'])
                        ->first();
                }

                if (!$programme) {
                    $available = Programme::where('is_active', 1)->pluck('libelle')->implode(', ');
                    throw new \Exception(
                        "Programme introuvable : «{$programmeVal}». Programmes actifs : [{$available}]"
                    );
                }

                // ══════════════════════════════════════════════════════════════
                //  5. RÉSOLUTION SITE — création automatique si inexistant
                // ══════════════════════════════════════════════════════════════
                $site = $this->resolveOrCreateSite($wilaya, $programme, $siteVal, $communeVal);

                // ══════════════════════════════════════════════════════════════
                //  6. RÉSOLUTION LOGEMENT — création automatique si inexistant
                // ══════════════════════════════════════════════════════════════
                $logement = $this->resolveOrCreateLogement(
                    $site, $programme,
                    $batiment, $etage, $porte,
                    $num_lot, $surface, $typologie, $prix
                );

                // ══════════════════════════════════════════════════════════════
                //  7. VÉRIFICATION NIN UNIQUE
                // ══════════════════════════════════════════════════════════════
                if (Souscripteur::where('nin', $nin)->exists()) {
                    throw new \Exception("NIN déjà enregistré : «{$nin}»");
                }

                // ══════════════════════════════════════════════════════════════
                //  8. GÉNÉRATION CODE LPL UNIQUE
                // ══════════════════════════════════════════════════════════════
                $codeLPL = $this->generateCodeLPL($logement);

                // ══════════════════════════════════════════════════════════════
                //  9. GÉNÉRATION QR CODE
                // ══════════════════════════════════════════════════════════════
                $qrPlain = implode(' | ', [
                    'AADL',
                    'Nom: '       . strtoupper($nom),
                    'Prénom: '    . $prenom,
                    'Programme: ' . $programme->libelle,
                    'Site: '      . $site->libelle,
                    'Code: '      . $codeLPL,
                ]);
                $qrHashed = hash('sha256', $qrPlain);
                $qrCode   = base64_encode(QrCode::size(200)->margin(1)->generate($qrHashed));

                // ══════════════════════════════════════════════════════════════
                //  10. MISE À JOUR LOGEMENT (flag → 1, code LPL, détails)
                // ══════════════════════════════════════════════════════════════
                $logement->update([
                    'code_loge_lpl' => $codeLPL,
                    'flag'          => 1,
                    'num_lot'       => $num_lot,
                    'surface'       => (float) $surface,
                    'typologie'     => $typologie,
                    'prix'          => (float) $prix,
                    'programme_id'  => $programme->id,
                    'user_id'       => Auth::id(),
                ]);

                // ══════════════════════════════════════════════════════════════
                //  11. CRÉATION SOUSCRIPTEUR
                // ══════════════════════════════════════════════════════════════
                Souscripteur::create([
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

                DB::commit();
                $this->imported++;

            } catch (\Exception $e) {
                DB::rollBack();
                $this->errors[] = "Ligne {$line} : " . $e->getMessage();
            }
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    //  MÉTHODES PRIVÉES
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Résout ou crée un site pour une wilaya + programme donnés.
     * Correspondance exacte d'abord, puis LIKE, puis création automatique.
     */
    private function resolveOrCreateSite(Wilaya $wilaya, Programme $programme, string $siteVal, string $communeVal): Site
    {
        // 1. Correspondance exacte
        $site = Site::where('wilaya_id', $wilaya->id)
            ->where('programme_id', $programme->id)
            ->whereRaw('LOWER(TRIM(libelle)) = ?', [strtolower($siteVal)])
            ->first();

        if ($site) {
            return $site;
        }

        // 2. Correspondance partielle (LIKE)
        $site = Site::where('wilaya_id', $wilaya->id)
            ->where('programme_id', $programme->id)
            ->whereRaw('LOWER(libelle) LIKE ?', ['%' . strtolower($siteVal) . '%'])
            ->first();

        if ($site) {
            return $site;
        }

        // 3. Résolution de la commune par nom dans la wilaya
        $commune = \App\Models\Commune::where('wilaya_id', $wilaya->id)
            ->whereRaw('LOWER(TRIM(nom)) = ?', [strtolower(trim($communeVal))])
            ->first();

        if (!$commune) {
            // Fallback LIKE
            $commune = \App\Models\Commune::where('wilaya_id', $wilaya->id)
                ->whereRaw('LOWER(nom) LIKE ?', ['%' . strtolower(trim($communeVal)) . '%'])
                ->first();
        }

        if (!$commune) {
            $available = \App\Models\Commune::where('wilaya_id', $wilaya->id)
                ->orderBy('nom')->pluck('nom')->take(10)->implode(', ');
            throw new \Exception(
                "Commune introuvable : «{$communeVal}» (wilaya: {$wilaya->nom}). "
                . "Exemples disponibles : [{$available}...]"
            );
        }

        // 4. Création automatique du site avec commune_id
        return Site::create([
            'libelle'      => $siteVal,
            'wilaya_id'    => $wilaya->id,
            'programme_id' => $programme->id,
            'commune_id'   => $commune->id,
            'user_id'      => Auth::id(),
        ]);
    }

    /**
     * Résout ou crée un logement pour un site donné.
     * Si le logement existe et est libre (flag 0 ou 3) → on l'utilise.
     * Si le logement n'existe pas → on le crée avec flag=0.
     * Si le logement est déjà attribué → exception.
     */
    private function resolveOrCreateLogement(
        Site $site, Programme $programme,
        string $batiment, string $etage, string $porte,
        string $num_lot, string $surface, string $typologie, string $prix
    ): Logement {
        $existing = Logement::where('site_id', $site->id)
            ->whereRaw('TRIM(CAST(num_batiment AS CHAR)) = ?', [trim($batiment)])
            ->whereRaw('num_etage = ?', [(int) $etage])
            ->whereRaw('num_porte = ?', [(int) $porte])
            ->first();

        if ($existing) {
            // Logement trouvé — vérifier disponibilité
            if (!in_array($existing->flag, [0, 3])) {
                $flagLabel = self::FLAG_LABELS[$existing->flag] ?? "flag={$existing->flag}";
                throw new \Exception(
                    "Logement déjà pris : Bât.{$batiment} Ét.{$etage} Porte {$porte} "
                    . "(site: {$site->libelle}) — statut : «{$flagLabel}»"
                    . ($existing->code_loge_lpl ? " — code LPL : {$existing->code_loge_lpl}" : '')
                );
            }
            return $existing;
        }

        // Logement inexistant → création automatique (flag=0 = libre)
        return Logement::create([
            'site_id'      => $site->id,
            'programme_id' => $programme->id,
            'num_batiment' => (int) $batiment,
            'num_etage'    => (int) $etage,
            'num_porte'    => (int) $porte,
            'num_lot'      => $num_lot,
            'surface'      => (float) $surface,
            'typologie'    => $typologie,
            'prix'         => (float) $prix,
            'flag'         => 0,
            'user_id'      => Auth::id(),
        ]);
    }

    /**
     * Génère un code LPL unique de la forme B{bat}N{porte}{random5}.
     */
    private function generateCodeLPL(Logement $logement): string
    {
        do {
            $random   = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $batPad   = str_pad((string) $logement->num_batiment, 2, '0', STR_PAD_LEFT);
            $portePad = str_pad((string) $logement->num_porte,    2, '0', STR_PAD_LEFT);
            $code     = "B{$batPad}N{$portePad}{$random}";
        } while (Logement::where('code_loge_lpl', $code)->exists());

        return $code;
    }

    /**
     * Parse une date Excel (numérique) ou une chaîne JJ/MM/AAAA → Y-m-d.
     */
    private function parseDate(mixed $raw): string
    {
        if ($raw === null || $raw === '') {
            return '';
        }
        if (is_numeric($raw)) {
            return ExcelDate::excelToDateTimeObject((float) $raw)->format('Y-m-d');
        }
        $str = trim((string) $raw);
        // Formats : JJ/MM/AAAA ou AAAA-MM-JJ
        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y'] as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $str);
            if ($dt && $dt->format($fmt) === $str) {
                return $dt->format('Y-m-d');
            }
        }
        return $str; // laisser la validation DB gérer l'erreur
    }

    /**
     * Nettoie un NIN : gère notation scientifique Excel, trim, espaces.
     */
    private function parseNin(mixed $raw): string
    {
        if ($raw === null) {
            return '';
        }
        $str = (string) $raw;
        // Notation scientifique (ex: 1.23456789012345E+17)
        if (is_numeric($raw) && stripos($str, 'E') !== false) {
            return number_format((float) $raw, 0, '.', '');
        }
        if (is_float($raw) || is_int($raw)) {
            return number_format($raw, 0, '.', '');
        }
        return preg_replace('/\s+/', '', trim($str));
    }

    /** Trim + cast string. */
    private function str(mixed $v): string
    {
        return trim((string) ($v ?? ''));
    }

    /** Vérifie qu'aucun champ obligatoire n'est vide. */
    private function requireAll(array $fields): void
    {
        foreach ($fields as $label => $val) {
            if ($val === '') {
                throw new \Exception("Champ obligatoire manquant : «{$label}»");
            }
        }
    }
}