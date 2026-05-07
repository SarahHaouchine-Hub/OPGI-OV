<?php

namespace Database\Seeders;

use App\Models\Logement;
use App\Models\Programme;
use App\Models\Site;
use App\Models\Wilaya;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogementsSeeder extends Seeder
{
    /**
     * Configuration des sites à peupler.
     *
     * Chaque entrée définit un site avec sa structure de bâtiments.
     * Format : wilaya → programme → site → [nb_batiments, nb_etages, nb_portes]
     */
    private array $config = [
        [
            'wilaya'       => 'Alger',
            'programme'    => 'LSP',
            'site'         => 'site 1 DAR EL BEIDA 1200 lgts',
            'nb_batiments' => 10,
            'nb_etages'    => 4,
            'nb_portes'    => 6,
        ],
        // ── Ajoutez vos autres sites ici ──────────────────────────────────
        // [
        //     'wilaya'       => 'Oran',
        //     'programme'    => 'AADL2',
        //     'site'         => 'Résidence Es-Senia',
        //     'nb_batiments' => 8,
        //     'nb_etages'    => 5,
        //     'nb_portes'    => 4,
        // ],
    ];

    public function run(): void
    {
        $created = 0;
        $skipped = 0;

        foreach ($this->config as $entry) {

            // ── Résolution wilaya ──────────────────────────────────────────
            $wilaya = Wilaya::whereRaw('LOWER(TRIM(nom)) = ?', [strtolower($entry['wilaya'])])->first();
            if (!$wilaya) {
                $this->command->warn("  ⚠  Wilaya introuvable : «{$entry['wilaya']}» — site ignoré.");
                continue;
            }

            // ── Résolution programme ───────────────────────────────────────
            $programme = Programme::where('is_active', 1)
                ->whereRaw('LOWER(TRIM(libelle)) = ?', [strtolower($entry['programme'])])
                ->first();
            if (!$programme) {
                $this->command->warn("  ⚠  Programme introuvable : «{$entry['programme']}» — site ignoré.");
                continue;
            }

            // ── Résolution site (exact puis LIKE) ──────────────────────────
            $site = Site::where('wilaya_id', $wilaya->id)
                ->where('programme_id', $programme->id)
                ->whereRaw('LOWER(TRIM(libelle)) = ?', [strtolower($entry['site'])])
                ->first()
                ?? Site::where('wilaya_id', $wilaya->id)
                    ->where('programme_id', $programme->id)
                    ->whereRaw('LOWER(libelle) LIKE ?', ['%' . strtolower($entry['site']) . '%'])
                    ->first();

            if (!$site) {
                $this->command->warn("  ⚠  Site introuvable : «{$entry['site']}» — ignoré.");
                continue;
            }

            $total = $entry['nb_batiments'] * $entry['nb_etages'] * $entry['nb_portes'];
            $this->command->info("  → Génération de {$total} logements pour «{$site->libelle}»...");

            // ── Génération de tous les logements ───────────────────────────
            DB::beginTransaction();
            try {
                for ($bat = 1; $bat <= $entry['nb_batiments']; $bat++) {
                    for ($etage = 1; $etage <= $entry['nb_etages']; $etage++) {
                        for ($porte = 1; $porte <= $entry['nb_portes']; $porte++) {

                            $exists = Logement::where('site_id',     $site->id)
                                ->where('num_batiment', $bat)
                                ->where('num_etage',    $etage)
                                ->where('num_porte',    $porte)
                                ->exists();

                            if ($exists) {
                                $skipped++;
                                continue;
                            }

                            Logement::create([
                                'site_id'      => $site->id,
                                'programme_id' => $programme->id,
                                'num_batiment' => $bat,
                                'num_etage'    => $etage,
                                'num_porte'    => $porte,
                                'flag'         => 0, // libre
                            ]);

                            $created++;
                        }
                    }
                }

                DB::commit();
                $this->command->info("     ✓ {$created} créés, {$skipped} déjà existants ignorés.");

            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error("  ✗ Erreur sur «{$site->libelle}» : " . $e->getMessage());
            }
        }

        $this->command->newLine();
        $this->command->info("  ══ Terminé : {$created} logement(s) créé(s), {$skipped} ignoré(s). ══");
    }
}