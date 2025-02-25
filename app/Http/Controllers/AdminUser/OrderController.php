<?php

namespace App\Http\Controllers\AdminUser;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(relations: ['orderItems.product', 'address', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders, 200);
    }

    public function show($id)
    {
        $order = Order::with(['orderItems.product', 'address', 'user'])
            ->find($id);

        if (!$order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        return response()->json($order, 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,paid,processing,shipped,delivered,cancelled',
        ], [
            'status.required' => 'Status harus diisi.',
            'status.in'       => 'Status tidak valid.',
        ]);

        $order = Order::find($id);
        if (!$order) {
            return response()->json([
                'message' => 'Pesanan tidak ditemukan.'
            ], 404);
        }

        $order->update([
            'status' => $data['status']
        ]);

        return response()->json([
            'message' => 'Status pesanan berhasil diperbarui.',
            'order'   => $order,
        ], 200);
    }
}
