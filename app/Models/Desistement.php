<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Desistement extends Model
{
    protected $fillable = [
        'souscripteur_id',
        'logement_id',
        'code_loge_lpl',
        'date_desistement',
        'user_id',
        'type',                    // ← nouveau
        'nouveau_souscripteur_id', // ← nouveau
    ];

    public function souscripteur()
    {
        return $this->belongsTo(Souscripteur::class);
    }

    public function logement()
    {
        return $this->belongsTo(Logement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Nouveau souscripteur affecté lors d'un remplacement
    public function nouveauSouscripteur()
    {
        return $this->belongsTo(Souscripteur::class, 'nouveau_souscripteur_id');
    }
}