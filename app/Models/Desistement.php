<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Desistement extends Model
{
    protected $fillable = ['souscripteur_id','logement_id', 'code_loge_lpl', 'date_desistement','user_id'];

    public function souscripteur(){
       return $this->belongsTo(Souscripteur::class);
    }

    public function logement(){
       return $this->belongsTo(Logement::class);
    }

    public function user(){
       return $this->belongsTo(User::class);
    }
}
