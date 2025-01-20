<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    
    protected $fillable = ['category_name', 'parent_category_id'];

    // Relasi ke subkategori
    public function subcategories()
    {
        return $this->hasMany(Category::class, 'parent_category_id');
    }

    // Relasi ke kategori induk
    public function parentCategory()
    {
        return $this->belongsTo(Category::class, 'parent_category_id');
    }

    // Relasi ke Produk
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
