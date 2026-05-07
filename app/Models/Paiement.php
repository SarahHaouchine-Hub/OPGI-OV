<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = [
            'ov_id',
            'num_recu',
            'date_paiement',
            'nom_agence',
            'num_agence',
            'recu_pdf',
            'user_id'

    ];


        public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ov(){
        return $this->belongsTo(Ov::class,'ov_id');
    }
}
