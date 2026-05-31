<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logement extends Model
{
    protected $fillable = [
        'num_batiment',
        'num_etage',
        'num_porte',
        'num_lot',
        'surface',
        'typologie',
        'code_loge_lpl',
        'flag',
        'prix',
        'site_id',
        'programme_id',
        'user_id',        // ← manquait
        'box_num',
    'box_superficie',
    'box_num_lot',
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