<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Souscripteur extends Model
{
    //

    protected $fillable = [
        'nom', 
        'prenom', 
        'date_naissance', 
         'nin',
       'qr_content_plain', 
    'qr_content_hashed',
    'qrcode', // <-- ASSUREZ-VOUS QUE C'EST BIEN ICI
        'code_loge_lpl', 
        'user_id',
        'nom_arabe',
        'prenom_arabe',
        'desiste'
    ];


public function logement()
{
    // On lie le souscripteur au logement via le code LPL
    return $this->belongsTo(Logement::class, 'code_loge_lpl', 'code_loge_lpl');
}

public function ovs(){
    return $this->hasMany(Ov::class,'souscripteur_id');
}

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

public function desistement()
{
    // Un souscripteur a (potentiellement) un seul enregistrement de désistement
    return $this->hasOne(Desistement::class);
}
public function aides()
{
    return $this->hasMany(Aide::class);
}
}
