<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commune extends Model
{
   protected $fillable = ['nom', 'wilaya_id', 'user_id'];

public function user()
{
    return $this->belongsTo(User::class);
}
    public function wilaya()
    {
        return $this->belongsTo(Wilaya::class);
    }

    public function sites()
    {
        return $this->hasMany(Site::class);
    }
}