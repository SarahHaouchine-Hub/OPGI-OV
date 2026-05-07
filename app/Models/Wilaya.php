<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wilaya extends Model
{
   protected $fillable = ['nom', 'is_active', 'user_id'];

public function user()
{
    return $this->belongsTo(User::class);
}

    public function communes()
    {
        return $this->hasMany(Commune::class);
    }

    public function sites()
    {
        return $this->hasMany(Site::class);
    }
}