<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logement extends Model
{
protected $fillable = [
    'num_batiment',   // string : A, B, C...
    'num_etage',
    'num_porte',
    'num_lot',
    'surface',
    'typologie',      // F3, F4, F5
    'code_loge_lpl',
    'flag',
    'prix',
    'created_by',
    'site_id',
    'programme_id',
];

    public function souscripteur()
    {
        return $this->belongsTo(Souscripteur::class, 'code_loge_lpl', 'code_loge_lpl');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function desistements()
    {
        return $this->hasMany(Desistement::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }
}