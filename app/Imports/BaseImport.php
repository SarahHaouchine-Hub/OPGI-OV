<?php

namespace App\Imports;

use App\Models\Commune;
use App\Models\Logement;
use App\Models\Paiement;
use App\Models\Programme;
use App\Models\Site;
use App\Models\Souscripteur;
use App\Models\Wilaya;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Classe de base partagée par LplImport, LspImport, LpaImport.
 *
 * CHANGELOG
 * ─────────────────────────────────────────────────────────────────────────────
 * v12 — resolveOrCreateSite() accepte deux nouveaux paramètres optionnels :
 *       • $adresseAgence   → sites.adresse_agence
 *       • $numCompteAgence → sites.num_compte_agence
 *       Même règle que v11 : on n'écrase jamais une valeur déjà renseignée.
 * v11 — resolveOrCreateSite() accepte num_convention_bnh, nom_agence, num_agence.
 * ─────────────────────────────────────────────────────────────────────────────
 */
abstract class BaseImport implements ToCollection, WithStartRow
{
    public array $errors   = [];
    public int   $imported = 0;

    protected const FLAG_LABELS = [
        0 => 'Libre',
        1 => 'Attribué',
        2 => 'En cours de paiement',
        3 => 'Désisté (disponible)',
    ];

    // ── Site ──────────────────────────────────────────────────────────────────

    /**
     * Résout ou crée un site.
     *
     * v12 : deux nouveaux paramètres optionnels :
     *   - $adresseAgence   → sites.adresse_agence
     *   - $numCompteAgence → sites.num_compte_agence
     */
  protected function resolveOrCreateSite(
    Wilaya    $wilaya,
    Programme $programme,
    string    $siteVal,
    string    $communeVal,
    string    $numConvBnh      = '',
    string    $nomAgence       = '',
    string    $numAgence       = '',
    string    $adresseAgence   = '',
    string    $numCompteAgence = '',
    string    $titulaire       = ''    // ← NOUVEAU
): Site {

    $site = Site::where('wilaya_id', $wilaya->id)
        ->where('programme_id', $programme->id)
        ->whereRaw('LOWER(TRIM(libelle)) = ?', [strtolower($siteVal)])
        ->first();

    if (!$site) {
        $site = Site::where('wilaya_id', $wilaya->id)
            ->where('programme_id', $programme->id)
            ->whereRaw('LOWER(libelle) LIKE ?', ['%'.strtolower($siteVal).'%'])
            ->first();
    }

    if ($site) {
        $toUpdate = [];
        if ($numConvBnh      !== '' && empty($site->num_convention_bnh)) $toUpdate['num_convention_bnh'] = $numConvBnh;
        if ($nomAgence       !== '' && empty($site->nom_agence))         $toUpdate['nom_agence']         = $nomAgence;
        if ($numAgence       !== '' && empty($site->num_agence))         $toUpdate['num_agence']         = $numAgence;
        if ($adresseAgence   !== '' && empty($site->adresse_agence))     $toUpdate['adresse_agence']     = $adresseAgence;
        if ($numCompteAgence !== '' && empty($site->num_compte_agence))  $toUpdate['num_compte_agence']  = $numCompteAgence;
        if ($titulaire       !== '' && empty($site->titulaire))          $toUpdate['titulaire']          = $titulaire;  // ← NOUVEAU
        if (!empty($toUpdate)) $site->update($toUpdate);
        return $site;
    }


        // ── Site introuvable → résolution de la commune puis création ─────
        $commune = Commune::where('wilaya_id', $wilaya->id)
            ->whereRaw('LOWER(TRIM(nom)) = ?', [strtolower(trim($communeVal))])
            ->first();

        if (!$commune) {
            $commune = Commune::where('wilaya_id', $wilaya->id)
                ->whereRaw('LOWER(nom) LIKE ?', ['%'.strtolower(trim($communeVal)).'%'])
                ->first();
        }

        if (!$commune) {
            $ex = Commune::where('wilaya_id', $wilaya->id)
                ->orderBy('nom')->pluck('nom')->take(8)->implode(', ');
            throw new \Exception(
                "Commune introuvable : «{$communeVal}» (wilaya: {$wilaya->nom}). Exemples: [{$ex}]"
            );
        }

        return Site::create([
            'libelle'            => $siteVal,
            'wilaya_id'          => $wilaya->id,
            'programme_id'       => $programme->id,
            'commune_id'         => $commune->id,
            'num_convention_bnh' => $numConvBnh      ?: null,
            'nom_agence'         => $nomAgence        ?: null,
            'num_agence'         => $numAgence        ?: null,
            'adresse_agence'     => $adresseAgence    ?: null,   // ← nouveau v12
            'num_compte_agence'  => $numCompteAgence  ?: null,   // ← nouveau v12
            'titulaire'          => $titulaire         ?: null,  // ← NOUVEAU
            'user_id'            => Auth::id(),
        ]);
    }

    // ── Logement ──────────────────────────────────────────────────────────────

    protected function resolveOrCreateLogement(
        Site $site, Programme $programme,
        string $batiment, string $etage, string $porte,
        string $num_lot, string $surface, string $typologie, string $prix
    ): Logement {
        $existing = Logement::where('site_id', $site->id)
            ->whereRaw('TRIM(CAST(num_batiment AS CHAR)) = ?', [trim($batiment)])
            ->whereRaw('num_etage = ?', [(int)$etage])
            ->whereRaw('num_porte = ?', [(int)$porte])
            ->first();

        if ($existing) {
            if (!in_array($existing->flag, [0, 3])) {
                $label = static::FLAG_LABELS[$existing->flag] ?? "flag={$existing->flag}";
                throw new \Exception(
                    "Logement déjà pris : Bât.{$batiment} Ét.{$etage} Porte {$porte}"
                    . " — statut : «{$label}»"
                    . ($existing->code_loge_lpl ? " (code: {$existing->code_loge_lpl})" : '')
                );
            }
            return $existing;
        }

        return Logement::create([
            'site_id'      => $site->id,
            'programme_id' => $programme->id,
            'num_batiment' => (int)$batiment,
            'num_etage'    => (int)$etage,
            'num_porte'    => (int)$porte,
            'num_lot'      => $num_lot,
            'surface'      => (float)$surface,
            'typologie'    => $typologie,
            'prix'         => (float)$prix,
            'flag'         => 0,
            'user_id'      => Auth::id(),
        ]);
    }

    // ── Code LPL unique ───────────────────────────────────────────────────────

    protected function generateCodeLPL(Logement $logement): string
    {
        do {
            $code = 'B' . str_pad((string)$logement->num_batiment, 2, '0', STR_PAD_LEFT)
                  . 'N' . str_pad((string)$logement->num_porte,    2, '0', STR_PAD_LEFT)
                  . str_pad((string)mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
        } while (Logement::where('code_loge_lpl', $code)->exists());
        return $code;
    }

    // ── QR Code ───────────────────────────────────────────────────────────────

    protected function buildQr(
        string $prog,
        Souscripteur $s,
        float $montant,
        ?int $tranche = null
    ): array {
        $plain = sprintf(
            'OPGI %s | Nom: %s | Prénom: %s | Code: %s%s | Montant: %.2f',
            $prog,
            strtoupper($s->nom),
            $s->prenom,
            $s->code_loge_lpl,
            $tranche ? " | Tranche: {$tranche}" : '',
            $montant
        );
        $hashed = hash('sha256', $plain);
        $svg    = base64_encode(QrCode::size(200)->margin(1)->generate($hashed));
        return [$plain, $hashed, $svg];
    }

    protected function buildQrSous(
        string $nom, string $prenom,
        string $progLibelle, string $siteLibelle, string $codeLPL
    ): array {
        $plain = implode(' | ', [
            'OPGI',
            'Nom: '       . strtoupper($nom),
            'Prénom: '    . $prenom,
            'Programme: ' . $progLibelle,
            'Site: '      . $siteLibelle,
            'Code: '      . $codeLPL,
        ]);
        $hashed = hash('sha256', $plain);
        $svg    = base64_encode(QrCode::size(200)->margin(1)->generate($hashed));
        return [$plain, $hashed, $svg];
    }

    // ── Paiement ──────────────────────────────────────────────────────────────

    protected function createPaiementIfPresent(array $arr, int $offset, int $ovId): void
    {
        $numRecu   = $this->str($arr[$offset]     ?? '');
        $nomAgence = $this->str($arr[$offset + 1] ?? '');
        $numAgence = $this->str($arr[$offset + 2] ?? '');
        $datePaie  = $this->parseDate($arr[$offset + 3] ?? '');

        if ($numRecu === '' && $nomAgence === '' && $numAgence === '' && $datePaie === '') {
            return;
        }

        if ($nomAgence === '') {
            throw new \Exception("Nom agence obligatoire quand la section paiement est renseignée.");
        }
        if ($datePaie === '') {
            throw new \Exception("Date paiement obligatoire quand la section paiement est renseignée.");
        }

        if ($numRecu !== '' && Paiement::where('num_recu', $numRecu)->exists()) {
            throw new \Exception("Numéro de reçu déjà utilisé : «{$numRecu}»");
        }

        Paiement::create([
            'ov_id'         => $ovId,
            'num_recu'      => $numRecu    ?: null,
            'nom_agence'    => $nomAgence,
            'num_agence'    => $numAgence  ?: null,
            'date_paiement' => $datePaie,
            'recu_pdf'      => '',
            'user_id'       => Auth::id(),
        ]);
    }

    // ── Parseurs ──────────────────────────────────────────────────────────────

    protected function parseDate(mixed $raw): string
    {
        if ($raw === null || $raw === '') return '';
        if (is_numeric($raw)) {
            return ExcelDate::excelToDateTimeObject((float)$raw)->format('Y-m-d');
        }
        $str = trim((string)$raw);
        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y', 'd/m/y'] as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $str);
            if ($dt && $dt->format($fmt) === $str) {
                return $dt->format('Y-m-d');
            }
        }
        return $str;
    }

    protected function parseNin(mixed $raw): string
    {
        if ($raw === null) return '';
        $str = (string)$raw;
        if (is_numeric($raw) && stripos($str, 'E') !== false) {
            return number_format((float)$raw, 0, '.', '');
        }
        if (is_float($raw) || is_int($raw)) {
            return number_format($raw, 0, '.', '');
        }
        return preg_replace('/\s+/', '', trim($str));
    }

    protected function num(mixed $v): float { return (float)($v ?? 0); }
    protected function str(mixed $v): string { return trim((string)($v ?? '')); }

    protected function requireAll(array $fields): void
    {
        foreach ($fields as $label => $val) {
            if ($val === '') {
                throw new \Exception("Champ obligatoire manquant : «{$label}»");
            }
        }
    }
}