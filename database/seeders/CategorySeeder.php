<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'category_name' => 'Celana',
                'image'         => 'C:\Users\USER\Downloads\product-as-denim\WEB-BANNER-GAMA.jpg',
                'slug'          => 'celana',
            ],
            [
                'category_name' => 'Kemeja',
                'image'         => 'C:\Users\USER\Downloads\product-as-denim\Indigo-slub-workshirt-LS-1.jpg',
                'slug'          => 'kemeja',
            ],
            [
                'category_name' => 'Kaos',
                'image'         => 'C:\Users\USER\Downloads\product-as-denim\greenday.jpeg',
                'slug'          => 'kaos',
            ],
            [
                'category_name' => 'Sepatu',
                'image'         => 'c:\Users\USER\Downloads\product-as-denim\sepatu1.jpeg',
                'slug'          => 'sepatu',
            ],
        ];

        foreach ($categories as $category) {
            if (file_exists($category['image'])) {
                $filename = 'categories/' . Str::random(20) . '.' . pathinfo($category['image'], PATHINFO_EXTENSION);

                Storage::disk('public')->put($filename, file_get_contents($category['image']));

                $category['image'] = $filename;
            } else {
                $category['image'] = 'categories/default.jpg';
            }

            Category::create($category);
        }
    }
}
