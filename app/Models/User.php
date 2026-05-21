<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const ROLES = [
        'admin'                => 'Administrateur',
        'dg'                   => 'DG',
        'dga'                  => 'DGA',
        'chef_service_com'     => 'Chef de service Commercial',
        'charge_etude_lsp_lpa' => 'Chargé d\'étude LSP/LPA',
        'charge_etude_prom'    => 'Chargé d\'étude Promotionnel',
        'agent'                => 'Agent',
    ];

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }

    public function logements()
    {
        return $this->hasMany(Logement::class);
    }

    public function souscripteurs()
    {
        return $this->hasMany(Souscripteur::class, 'created_by');
    }

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }
}