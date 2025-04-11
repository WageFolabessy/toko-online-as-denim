<?php

namespace App\Http\Controllers\SiteUser;

use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public function getUserOrder(Request $request)
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 10);

        $orderQuery = Order::where('site_user_id', $user->id);

        $orders = $orderQuery->with([
            'payment:id,order_id,status,payment_type',
            'orderItems' => function ($query) {
                $query->limit(1);
            },
            'orderItems.product' => function ($query) {
                $query->select('id', 'product_name', 'slug');
            },
            'orderItems.product.images' => function ($query) {
                $query->where('is_primary', true)->select('id', 'product_id', 'image', 'is_primary');
            }
        ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return OrderResource::collection($orders);
    }

    public function showUserOrder(Request $request, $id)
    {
        $user = $request->user();

        $order = Order::with([
            'orderItems.product' => function ($query) {
                $query->select('id', 'product_name', 'slug', 'weight', 'original_price', 'sale_price');
            },
            'orderItems.product.images' => function ($query) {
                $query->where('is_primary', true)->select('id', 'product_id', 'image', 'is_primary');
            },
            'address',
            'shipment',
            'payment'
        ])
            ->where('site_user_id', $user->id)
            ->findOrFail($id);

        return new OrderResource($order);
    }
}
