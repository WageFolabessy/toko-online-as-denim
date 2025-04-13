<?php

namespace App\Http\Controllers\SiteUser;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    public function getCartRecommendations(Request $request)
    {
        $user = Auth::user(); // Dapatkan user yang sedang login

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Ambil item di keranjang user (asumsi relasi 'shoppingCart' dan 'items' sudah ada di model SiteUser)
        $cartItems = $user->shoppingCart->with('items.product')->first(); // Ambil keranjang beserta item & produknya

        if (!$cartItems || $cartItems->items->isEmpty()) {
            // Jika keranjang kosong, mungkin tampilkan produk populer atau terbaru saja
            $recommendations = Product::with('primaryImage')
                ->where('stock', '>', 0) // Hanya produk yang ada stok
                ->orderBy('updated_at', 'desc') // Ambil yang terbaru
                ->limit(6)
                ->get();
            return response()->json($recommendations);
        }

        // Ambil ID produk dan ID kategori dari item di keranjang
        $productIdsInCart = $cartItems->items->pluck('product_id')->toArray();
        $categoryIdsInCart = $cartItems->items->pluck('product.category_id')->unique()->toArray();

        // Cari produk lain di kategori yang sama, kecualikan yang sudah ada di keranjang
        $recommendations = Product::with('primaryImage')
            ->whereIn('category_id', $categoryIdsInCart) // Dari kategori yang sama
            ->whereNotIn('id', $productIdsInCart) // Bukan produk yg sudah di cart
            ->where('stock', '>', 0) // Hanya yang ada stok
            ->inRandomOrder() // Ambil secara acak
            ->limit(6) // Batasi jumlah rekomendasi
            ->get();

        // Jika hasil kurang dari limit, mungkin tambahkan produk populer/terbaru lain
        if ($recommendations->count() < 6) {
            $needed = 6 - $recommendations->count();
            $excludeIds = array_merge($productIdsInCart, $recommendations->pluck('id')->toArray());

            $additionalRecs = Product::with('primaryImage')
                ->whereNotIn('id', $excludeIds) // Jangan ambil yg sudah ada
                ->where('stock', '>', 0)
                ->orderBy('updated_at', 'desc')
                ->limit($needed)
                ->get();

            $recommendations = $recommendations->merge($additionalRecs);
        }


        return response()->json($recommendations);
    }
}
