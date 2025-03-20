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
        $categories = Category::orderBy('updated_at', 'desc')->get();
        return response()->json($categories, 200);
    }

    public function getAllProducts()
    {
        $products = Product::with(['images', 'category'])->orderBy('updated_at', 'desc')->get();
        return response()->json($products, 200);
    }

    public function getLatesProducts()
    {
        $products = Product::with(['images', 'category'])
            ->where('stock', '>', 0)
            ->latest()
            ->take(5)
            ->get();

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
