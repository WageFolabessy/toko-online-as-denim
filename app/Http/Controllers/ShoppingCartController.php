<?php

namespace App\Http\Controllers;

use App\Models\ShoppingCart;
use App\Models\ShoppingCartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShoppingCartController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Menggunakan eager loading untuk menyertakan data produk dan gambar
        $cart = ShoppingCart::where('site_user_id', $user->id)
            ->with('items.product.images') // Menyertakan relasi 'images' dari produk
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            // Mengembalikan array kosong jika keranjang kosong
            return response()->json([]);
        }

        return response()->json($cart->items);
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1'
        ]);

        $user = Auth::user();
        $cart = ShoppingCart::firstOrCreate(['site_user_id' => $user->id]);

        $cartItem = ShoppingCartItem::where('shopping_cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($cartItem) {
            $cartItem->increment('qty', $request->qty);
        } else {
            ShoppingCartItem::create([
                'shopping_cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'qty' => $request->qty
            ]);
        }

        return response()->json(['message' => 'Produk berhasil ditambahkan ke keranjang']);
    }

    public function updateCartItem(Request $request, $id)
    {
        $request->validate([
            'qty' => 'required|integer|min:1'
        ]);

        $user = Auth::user();
        $cartItem = ShoppingCartItem::whereHas('shoppingCart', function ($query) use ($user) {
            $query->where('site_user_id', $user->id);
        })->findOrFail($id);

        $cartItem->update(['qty' => $request->qty]);

        return response()->json(['message' => 'Jumlah produk berhasil diperbarui']);
    }

    public function removeCartItem($id)
    {
        $user = Auth::user();
        $cartItem = ShoppingCartItem::whereHas('shoppingCart', function ($query) use ($user) {
            $query->where('site_user_id', $user->id);
        })->findOrFail($id);

        $cartItem->delete();

        return response()->json(['message' => 'Produk berhasil dihapus dari keranjang']);
    }
}
