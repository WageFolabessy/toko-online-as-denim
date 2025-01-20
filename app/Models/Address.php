<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Address extends Model
{
    use HasFactory;
    
    protected $fillable = ['recipient_name', 'address_line1', 'address_line2', 'province', 'city', 'postal_code'];

    // Relasi ke SiteUserAddress
    public function userAddresses()
    {
        return $this->hasMany(SiteUserAddress::class, 'address_id');
    }
}
