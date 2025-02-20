<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SiteUser;
use App\Models\Address;
use App\Models\AdminUser;
use App\Models\SiteUserAddress;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ShoppingCart;
use App\Models\ShoppingCartItem;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        AdminUser::factory()->create();

        // SiteUser::factory(5)->create();
        // Address::factory(5)->create();
        // SiteUserAddress::factory(5)->create();
        // Category::factory(5)->create();
        // Product::factory(5)->create();
        // ProductImage::factory(5)->create();
        // ShoppingCart::factory(5)->create();
        // ShoppingCartItem::factory(5)->create();
    }
}
