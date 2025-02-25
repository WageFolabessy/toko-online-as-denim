<?php

namespace App\Http\Controllers\SiteUser;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{    
    public function getUserOrder(Request $request)
    {
        $user = $request->user();

        // Mengambil pesanan milik pengguna saat ini
        $orders = Order::with(['orderItems.product', 'address'])
            ->where('site_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders, 200);
    }

    public function showUserOrder(Request $request, $id)
    {
        $user = $request->user();

        // Mengambil pesanan dengan relasi, milik pengguna saat ini
        $order = Order::with(['orderItems.product', 'address'])
            ->where('id', $id)
            ->where('site_user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        return response()->json($order, 200);
    }
}
