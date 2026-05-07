<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ov extends Model
{
    protected $table = 'ordres_versement';

    protected $fillable = [
        'souscripteur_id',
        'montant_total',
        'pourcentage',
        'montant_paye',
        'montant_restant',
        'numero_tranche',    // ← NOUVEAU
        'vsp',               // ← NOUVEAU
        'qr_content_plain',
        'qr_content_hashed',
        'qrcode',
        'user_id',
    ];

    protected $casts = [
        'vsp' => 'boolean',  // ← NOUVEAU
    ];

    public function souscripteur()
    {
        return $this->belongsTo(Souscripteur::class, 'souscripteur_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paiement()
    {
        return $this->hasOne(Paiement::class);
    }
}
