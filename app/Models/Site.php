<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable = [
        'libelle',
        'wilaya_id',
        'commune_id',
        'programme_id',
        'num_convention_bnh',
        'nom_agence',
        'num_agence',
        'adresse_agence',      // ← NOUVEAU
        'num_compte_agence',   // ← NOUVEAU
         'titulaire',   // ← NOUVEAU
        'user_id',
    ];

    public function wilaya()
    {
        return $this->belongsTo(Wilaya::class);
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function logements()
    {
        return $this->hasMany(Logement::class);
    }
}