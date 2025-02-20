<?php

namespace App\Http\Controllers\SiteUser;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function getAllCategories()
    {
        $categories = Category::all();
        return response()->json($categories, 200);
    }

    public function getAllProducts()
    {
        $products = Product::with(['images', 'category'])->get();
        return response()->json($products, 200);
    }

    public function getProductDetail($slug)
    {
        $product = Product::with('images')->where('slug', $slug)->first();

        if (!$product) {
            return response()->json([
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'product' => $product,
        ], 200);
    }
}
