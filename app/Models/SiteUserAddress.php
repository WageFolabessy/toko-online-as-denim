<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SiteUserAddress extends Model
{
    use HasFactory;

    protected $fillable = ['site_user_id', 'address_id', 'is_default'];

    // Relasi ke SiteUser
    public function user()
    {
        return $this->belongsTo(SiteUser::class, 'site_user_id');
    }

    // Relasi ke Address
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }
}
