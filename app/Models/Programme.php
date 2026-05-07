<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programme extends Model
{
   protected $fillable = ['libelle', 'is_active', 'user_id'];

public function user()
{
    return $this->belongsTo(User::class);
}

    public function sites()
    {
        return $this->hasMany(Site::class);
    }

    public function logements()
    {
        return $this->hasMany(Logement::class);
    }
}