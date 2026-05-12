<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Souscripteur extends Model
{
    protected $fillable = [
        // ── Identité principale ──────────────────────────────────────
        'nom',
        'prenom',
        'date_naissance',
        'nin',

        // ── État civil & lieu de naissance ───────────────────────────
        'situation_familiale',   // celibataire | marie | divorce | veuf
        'lieu_naissance',

        // ── Parents du souscripteur (FR) ─────────────────────────────
        'nom_pere',
        'prenom_pere',
        'nom_mere',
        'prenom_mere',

        // ── Conjoint (FR) ────────────────────────────────────────────
        'conjoint_nom',
        'conjoint_prenom',
        'conjoint_nin',
        'conjoint_date_naissance',
        'conjoint_lieu_naissance',

        // ── Parents du conjoint (FR) ─────────────────────────────────
        'conjoint_nom_pere',
        'conjoint_prenom_pere',
        'conjoint_nom_mere',
        'conjoint_prenom_mere',

        // ── Champs système ───────────────────────────────────────────
        'qr_content_plain',
        'qr_content_hashed',
        'qrcode',
        'code_loge_lpl',
        'user_id',
        'desiste',
    ];

    protected $casts = [
        'date_naissance'          => 'date',
        'conjoint_date_naissance' => 'date',
    ];

    // Relations (inchangées)
    public function logement()
    {
        return $this->belongsTo(Logement::class, 'code_loge_lpl', 'code_loge_lpl');
    }

    public function ovs()
    {
        return $this->hasMany(Ov::class, 'souscripteur_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function desistement()
    {
        return $this->hasOne(Desistement::class);
    }

    public function aides()
    {
        return $this->hasMany(Aide::class);
    }

    public function isMarie(): bool
    {
        return $this->situation_familiale === 'marie';
    }
    public function creditBancaire()
{
    return $this->hasOne(CreditBancaire::class);
}
}