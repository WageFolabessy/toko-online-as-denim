<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class SiteUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'phone_number',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Relasi ke SiteUserAddress
    public function addresses()
    {
        return $this->hasMany(SiteUserAddress::class, 'site_user_id');
    }

    // Relasi ke ShoppingCart
    public function shoppingCart()
    {
        return $this->hasOne(ShoppingCart::class, 'site_user_id');
    }
}
