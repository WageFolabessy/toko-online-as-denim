<?php

namespace App\Http\Controllers\SiteUser;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Product;
use App\Models\ShoppingCart;
use App\Models\ShoppingCartItem;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ShoppingCartController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $cart = ShoppingCart::firstOrCreate(['site_user_id' => $user->id]);

        // Menggunakan eager loading untuk menyertakan data produk dan gambar
        $cartItems = ShoppingCartItem::with('product.images')
            ->where('shopping_cart_id', $cart->id)
            ->get();

        return response()->json($cartItems, 200);
    }

    public function addToCart(AddToCartRequest $request)
    {
        $user = Auth::user();
        $product = Product::find($request->product_id);

        // Mengecek ketersediaan stok
        if ($product->stock < $request->qty) {
            return response()->json([
                'message' => 'Stok produk tidak mencukupi.'
            ], 400);
        }

        $cart = ShoppingCart::firstOrCreate(['site_user_id' => $user->id]);

        $cartItem = ShoppingCartItem::firstOrCreate(
            [
                'shopping_cart_id' => $cart->id,
                'product_id'       => $product->id,
            ],
            [
                'qty' => 0,
            ]
        );

        $cartItem->qty += $request->qty;
        $cartItem->save();

        return response()->json(['message' => 'Produk berhasil ditambahkan ke keranjang'], 201);
    }

    public function updateCartItem(UpdateCartItemRequest $request, $id)
    {
        $user = Auth::user();
        $cartItem = ShoppingCartItem::whereHas('shoppingCart', function ($query) use ($user) {
            $query->where('site_user_id', $user->id);
        })->findOrFail($id);

        // Mengecek ketersediaan stok
        if ($cartItem->product->stock < $request->qty) {
            return response()->json([
                'message' => 'Stok produk tidak mencukupi.'
            ], 400);
        }

        $cartItem->update(['qty' => $request->qty]);

        return response()->json(['message' => 'Jumlah produk berhasil diperbarui'], 200);
    }

    public function removeCartItem($id)
    {
        $user = Auth::user();
        $cartItem = ShoppingCartItem::whereHas('shoppingCart', function ($query) use ($user) {
            $query->where('site_user_id', $user->id);
        })->findOrFail($id);

        $cartItem->delete();

        return response()->json(['message' => 'Produk berhasil dihapus dari keranjang'], 200);
    }
}
