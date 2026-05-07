<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
   protected $fillable = ['libelle', 'wilaya_id', 'commune_id', 'programme_id', 'user_id'];

public function user()
{
    return $this->belongsTo(User::class);
}

    public function wilaya()
    {
        return $this->belongsTo(Wilaya::class);
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }

    public function logements()
    {
        return $this->hasMany(Logement::class);
    }
}