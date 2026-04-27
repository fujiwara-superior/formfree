<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $keyType  = 'string';
    public    $incrementing = false;

    protected $fillable = [
        'id', 'company_id', 'name', 'email', 'password', 'role',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    // ─── リレーション ──────────────────────────────────────
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
