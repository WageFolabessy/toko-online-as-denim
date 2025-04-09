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

        $orders = Order::with(['orderItems.product', 'address', 'payment'])
            ->where('site_user_id', $user->id)
            ->whereHas('payment', function ($query) {
                $query->where('status', 'settlement');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders, 200);
    }

    public function showUserOrder(Request $request, $id)
    {
        $user = $request->user();

        $order = Order::with(['orderItems.product', 'address', 'shipment', 'payment'])
            ->where('id', $id)
            ->where('site_user_id', $user->id)
            ->whereHas('payment', function ($query) {
                $query->where('status', 'settlement');
            })
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        return response()->json($order, 200);
    }
}
