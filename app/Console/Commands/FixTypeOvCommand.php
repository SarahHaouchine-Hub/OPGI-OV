// app/Console/Commands/FixTypeOvCommand.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Souscripteur;

class FixTypeOvCommand extends Command
{
    protected $signature   = 'ov:fix-type';
    protected $description = 'Répare type_ov pour les OVs crédit existants';

    public function handle()
    {
        $souscripteurs = Souscripteur::with([
            'creditBancaire',
            'ovs' => fn($q) => $q->orderBy('numero_tranche')
        ])->whereHas('creditBancaire')->get();

        $fixed = 0;

        foreach ($souscripteurs as $s) {
            if (!$s->creditBancaire) continue;

            $diff = $s->creditBancaire->montant_attestation
                  - $s->creditBancaire->montant_reel;

            foreach ($s->ovs as $ov) {
                // T1 = numero_tranche 1 → type_ov doit rester null
                if ($ov->numero_tranche === 1) {
                    if ($ov->type_ov !== null) {
                        DB::table('ordres_versement')
                            ->where('id', $ov->id)
                            ->update(['type_ov' => null]);
                        $fixed++;
                        $this->line("  T1 remis à null : OV#{$ov->id}");
                    }
                    continue;
                }

                // T2 = numero_tranche 2 → credit_reel
                if ($ov->numero_tranche === 2 && $ov->type_ov !== 'credit_reel') {
                    DB::table('ordres_versement')
                        ->where('id', $ov->id)
                        ->update(['type_ov' => 'credit_reel']);
                    $fixed++;
                    $this->line("  T2 corrigé → credit_reel : OV#{$ov->id}");
                }

                // T3 = numero_tranche 3 → credit_diff
                if ($ov->numero_tranche === 3 && $ov->type_ov !== 'credit_diff') {
                    DB::table('ordres_versement')
                        ->where('id', $ov->id)
                        ->update(['type_ov' => 'credit_diff']);
                    $fixed++;
                    $this->line("  T3 corrigé → credit_diff : OV#{$ov->id}");
                }
            }
        }

        $this->info("✅ {$fixed} OV(s) corrigé(s).");
    }
}