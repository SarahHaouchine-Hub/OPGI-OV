<?php

namespace App\Imports;

use App\Models\Logement;
use App\Models\Programme;
use App\Models\Site;
use App\Models\Wilaya;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class LogementsImport implements ToCollection
{
    public array $errors   = [];
    public int   $imported = 0;   // nombre de logements créés
    public int   $skipped  = 0;   // logements déjà existants, ignorés

    public function collection(Collection $rows)
    {
        foreach ($rows as $i => $row) {
            // Ignorer les 5 premières lignes (titre, instruction, headers, mapping, séparateur)
            if ($i < 5) continue;

            $line = $i + 1;
            $arr  = $row->toArray();

            if (empty(array_filter($arr, fn($v) => $v !== null && $v !== ''))) continue;

            DB::beginTransaction();
            try {
                // ══════════════════════════════════════════════════════════════
                //  LECTURE DES COLONNES
                // ══════════════════════════════════════════════════════════════
                $wilayaVal    = trim((string) ($arr[0] ?? ''));
                $programmeVal = trim((string) ($arr[1] ?? ''));
                $siteVal      = trim((string) ($arr[2] ?? ''));
                $nbBatiments  = (int) ($arr[3] ?? 0);
                $nbEtages     = (int) ($arr[4] ?? 0);
                $nbPortes     = (int) ($arr[5] ?? 0);
                $flag         = isset($arr[6]) && is_numeric($arr[6]) ? (int) $arr[6] : 0;

                // ══════════════════════════════════════════════════════════════
                //  VALIDATION
                // ══════════════════════════════════════════════════════════════
                foreach ([
                    'wilaya'      => $wilayaVal,
                    'programme'   => $programmeVal,
                    'site'        => $siteVal,
                ] as $label => $val) {
                    if ($val === '') throw new \Exception("Champ manquant : «{$label}»");
                }

                if ($nbBatiments <= 0) throw new \Exception("nb_batiments doit être > 0 (valeur : «{$arr[3]}»)");
                if ($nbEtages   <= 0) throw new \Exception("nb_etages doit être > 0 (valeur : «{$arr[4]}»)");
                if ($nbPortes   <= 0) throw new \Exception("nb_portes doit être > 0 (valeur : «{$arr[5]}»)");
                if (!in_array($flag, [0, 3]))  throw new \Exception("flag invalide : «{$flag}» (0 = libre, 3 = désisté)");

                // ══════════════════════════════════════════════════════════════
                //  RÉSOLUTION WILAYA / PROGRAMME / SITE
                // ══════════════════════════════════════════════════════════════
                $wilaya = Wilaya::whereRaw('LOWER(TRIM(nom)) = ?', [strtolower($wilayaVal)])->first();
                if (!$wilaya) {
                    $available = Wilaya::orderBy('nom')->pluck('nom')->implode(', ');
                    throw new \Exception("Wilaya introuvable : «{$wilayaVal}». Valeurs acceptées : {$available}");
                }

                $programme = Programme::where('is_active', 1)
                    ->whereRaw('LOWER(TRIM(libelle)) = ?', [strtolower($programmeVal)])
                    ->first();
                if (!$programme) {
                    $available = Programme::where('is_active', 1)->pluck('libelle')->implode(', ');
                    throw new \Exception("Programme introuvable : «{$programmeVal}». Actifs : {$available}");
                }

                $site = Site::where('wilaya_id', $wilaya->id)
                    ->where('programme_id', $programme->id)
                    ->whereRaw('LOWER(TRIM(libelle)) = ?', [strtolower($siteVal)])
                    ->first()
                    ?? Site::where('wilaya_id', $wilaya->id)
                        ->where('programme_id', $programme->id)
                        ->whereRaw('LOWER(libelle) LIKE ?', ['%' . strtolower($siteVal) . '%'])
                        ->first();

                if (!$site) {
                    $available = Site::where('wilaya_id', $wilaya->id)
                        ->where('programme_id', $programme->id)
                        ->pluck('libelle')->implode(' | ');
                    throw new \Exception(
                        "Site introuvable : «{$siteVal}»."
                        . ($available ? " Sites existants : [{$available}]" : " Aucun site pour cette wilaya+programme.")
                    );
                }

                // ══════════════════════════════════════════════════════════════
                //  GÉNÉRATION DES LOGEMENTS
                //  Pour chaque bâtiment → chaque étage → chaque porte
                // ══════════════════════════════════════════════════════════════
                $createdCount = 0;
                $skippedCount = 0;

                for ($bat = 1; $bat <= $nbBatiments; $bat++) {
                    for ($etage = 1; $etage <= $nbEtages; $etage++) {
                        for ($porte = 1; $porte <= $nbPortes; $porte++) {

                            // Vérifier si ce logement existe déjà (idempotent)
                            $exists = Logement::where('site_id',     $site->id)
                                ->where('num_batiment', $bat)
                                ->where('num_etage',    $etage)
                                ->where('num_porte',    $porte)
                                ->exists();

                            if ($exists) {
                                $skippedCount++;
                                continue;
                            }

                            Logement::create([
                                'site_id'      => $site->id,
                                'programme_id' => $programme->id,
                                'num_batiment' => $bat,
                                'num_etage'    => $etage,
                                'num_porte'    => $porte,
                                'flag'         => $flag,
                                'user_id'      => Auth::id(),
                            ]);

                            $createdCount++;
                        }
                    }
                }

                DB::commit();

                $this->imported += $createdCount;
                $this->skipped  += $skippedCount;

                if ($skippedCount > 0) {
                    $this->errors[] = "Ligne {$line} (info) : {$skippedCount} logement(s) déjà existants ignorés pour le site «{$site->libelle}».";
                }

            } catch (\Exception $e) {
                DB::rollBack();
                $this->errors[] = "Ligne {$line} : " . $e->getMessage();
            }
        }
    }
}