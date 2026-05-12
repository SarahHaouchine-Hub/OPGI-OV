<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditBancaire extends Model
{
    protected $table = 'credits_bancaires';

    protected $fillable = [
        'souscripteur_id',
        'montant_attestation',
        'montant_reel',
        'difference',
        'date_attestation',
        'date_versement_reel',
        'pieces_jointes',
        'user_id',
    ];

    protected $casts = [
        'montant_attestation'  => 'decimal:2',
        'montant_reel'         => 'decimal:2',
        'difference'           => 'decimal:2',
        'date_attestation'     => 'date',
        'date_versement_reel'  => 'date',
    ];

    // ── Relations ────────────────────────────────────────────────────

    public function souscripteur()
    {
        return $this->belongsTo(Souscripteur::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Mutateur pour calculer automatiquement la différence ────────

    public static function boot()
    {
        parent::boot();

        static::saving(function ($credit) {
            $credit->difference = $credit->montant_attestation - $credit->montant_reel;
        });
    }
}