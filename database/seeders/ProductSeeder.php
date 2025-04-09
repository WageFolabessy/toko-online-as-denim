<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\ProductImage;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'product_name'   => 'WINGMAN INDIGO SLUB WORK SHIRT L/S',
                'category_id'    => 2,
                'original_price' => 200000,
                'sale_price'     => 150000,
                'size'           => 'S',
                'stock'          => 1,
                'weight'         => 500,
                'description'    => 'Kemeja pria model slim fit.',
                'images'         => [
                    [
                        'image'       => 'c:\Users\USER\Downloads\product-as-denim\Indigo-slub-workshirt-LS-1.jpg',
                        'is_primary' => true,
                    ],
                    [
                        'image'       => 'c:\Users\USER\Downloads\product-as-denim\Indigo-slub-workshirt-LS-2.jpg',
                        'is_primary' => false,
                    ],
                    [
                        'image'       => 'c:\Users\USER\Downloads\product-as-denim\Indigo-slub-workshirt-LS-3.jpg',
                        'is_primary' => false,
                    ],
                    [
                        'image'       => 'c:\Users\USER\Downloads\product-as-denim\Indigo-slub-workshirt-LS-8.jpg',
                        'is_primary' => false,
                    ],
                ],
            ],
            [
                'product_name'   => 'WINGMAN HERRINGBONE BLACK WESTERN SHIRT L/S',
                'category_id'    => 2,
                'original_price' => 385000,
                'size'           => 'S',
                'stock'          => 1,
                'weight'         => 500,
                'description'    => 'Kemeja pria model slim fit.',
                'images'         => [
                    [
                        'image'       => 'c:\Users\USER\Downloads\product-as-denim\Herringbone-Western-Shirt-1.jpg',
                        'is_primary' => true,
                    ],
                    [
                        'image'       => 'c:\Users\USER\Downloads\product-as-denim\Herringbone-Western-Shirt-2.jpg',
                        'is_primary' => false,
                    ],
                    [
                        'image'       => 'c:\Users\USER\Downloads\product-as-denim\Herringbone-Western-Shirt-3-510x638.jpg',
                        'is_primary' => false,
                    ],
                ],
            ],
            [
                'product_name'   => 'WINGMAN GAMA 14.5oz',
                'category_id'    => 1,
                'original_price' => 785000,
                'sale_price'     => 685000,
                'size'           => '28 Slim Straight - Medium Rise',
                'stock'          => 1,
                'weight'         => 500,
                'description'    => 'Celana.',
                'images'         => [
                    [
                        'image'       => 'c:\Users\USER\Downloads\product-as-denim\Katalog-GAMA-1-2000x2500.jpg',
                        'is_primary' => true,
                    ],
                    [
                        'image'       => 'c:\Users\USER\Downloads\product-as-denim\Katalog-GAMA-3-2000x2500.jpg',
                        'is_primary' => false,
                    ],
                    [
                        'image'       => 'c:\Users\USER\Downloads\product-as-denim\Katalog-GAMA-4-1229x1536.jpg',
                        'is_primary' => false,
                    ],
                ],
            ],
            [
                'product_name'   => 'WINGMAN YASUKE | NH-207',
                'category_id'    => 1,
                'original_price' => 900000,
                'size'           => '28 Slim Fit - Medium Rise',
                'stock'          => 1,
                'weight'         => 500,
                'description'    => 'Celana',
                'images'         => [
                    [
                        'image'       => 'c:\Users\USER\Downloads\product-as-denim\Yasuke-COVER-1-1.jpg',
                        'is_primary' => true,
                    ],
                    [
                        'image'       => 'c:\Users\USER\Downloads\product-as-denim\Yasuke-7.jpg',
                        'is_primary' => false,
                    ],
                    [
                        'image'       => 'c:\Users\USER\Downloads\product-as-denim\Yasuke-10.jpg',
                        'is_primary' => false,
                    ],
                ],
            ],
            [
                'product_name'   => 'WINGMAN DUCK WHITE',
                'category_id'    => 3,
                'original_price' => 350000,
                'size'           => 'S Heavy Weight',
                'stock'          => 1,
                'weight'         => 200,
                'description'    => 'Kaos',
                'images'         => [
                    [
                        'image'       => 'c:\Users\USER\Downloads\product-as-denim\Tshirt-Duck-white-1.jpg',
                        'is_primary' => true,
                    ],
                    [
                        'image'       => 'c:\Users\USER\Downloads\product-as-denim\Tshirt-Duck-white-2.jpg',
                        'is_primary' => false,
                    ],
                    [
                        'image'       => 'c:\Users\USER\Downloads\product-as-denim\Tshirt-Duck-white-4.jpg',
                        'is_primary' => false,
                    ],
                ],
            ],
            [
                'product_name'   => 'Green Day Shirt',
                'category_id'    => 3,
                'original_price' => 145000,
                'size'           => 'S',
                'stock'          => 1,
                'weight'         => 200,
                'description'    => 'Kaos',
                'images'         => [
                    [
                        'image'       => 'c:\Users\USER\Downloads\product-as-denim\greenday.jpeg',
                        'is_primary' => true,
                    ],
                    [
                        'image'       => 'c:\Users\USER\Downloads\product-as-denim\green-day-shirts.png',
                        'is_primary' => false,
                    ],
                ],
            ],
            [
                'product_name'   => 'VANS AUTENTHIC',
                'category_id'    => 4,
                'original_price' => 650000,
                'size'           => '28 UK',
                'stock'          => 1,
                'weight'         => 100,
                'description'    => 'Sepatu',
                'images'         => [
                    [
                        'image'       => 'c:\Users\USER\Downloads\product-as-denim\sepatu.jpeg',
                        'is_primary' => true,
                    ]
                ],
            ],
        ];

        foreach ($products as $productData) {
            // Ambil data gambar dan hapus dari array produk
            $images = $productData['images'] ?? [];
            unset($productData['images']);

            // Buat record produk
            $product = Product::create($productData);

            // Proses setiap gambar produk
            foreach ($images as $image) {
                // Pastikan file gambar ada
                if (file_exists($image['image'])) {
                    // Buat nama file unik dan tentukan path penyimpanan
                    $filename = 'products/' . Str::random(20) . '.' . pathinfo($image['image'], PATHINFO_EXTENSION);

                    // Pindahkan file gambar ke storage public
                    Storage::disk('public')->put($filename, file_get_contents($image['image']));

                    // Update path gambar agar tersimpan di database
                    $image['image'] = $filename;
                } else {
                    // Jika file tidak ditemukan, gunakan gambar default
                    $image['image'] = 'products/default.jpg';
                }

                // Tetapkan foreign key product_id untuk relasi
                $image['product_id'] = $product->id;

                // Simpan record di table product_images
                ProductImage::create($image);
            }
        }
    }
}
