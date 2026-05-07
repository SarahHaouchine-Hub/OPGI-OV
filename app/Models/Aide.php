<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aide extends Model
{
    protected $table = 'aides';

    protected $fillable = [
        'souscripteur_id',
        'type',         // 'cnl' | 'fnpos'
        'montant',
        'num_convention', // CNL uniquement
        'num_decision',
        'date',
        'pieces_jointes',
        'user_id',
    ];

    protected $casts = [
        'date'    => 'date',
        'montant' => 'decimal:2',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function souscripteur()
    {
        return $this->belongsTo(Souscripteur::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'cnl'   => 'CNL',
            'fnpos' => 'FNPOS',
            default => strtoupper($this->type),
        };
    }

    public function isCnl(): bool
    {
        return $this->type === 'cnl';
    }

    public function isFnpos(): bool
    {
        return $this->type === 'fnpos';
    }
}