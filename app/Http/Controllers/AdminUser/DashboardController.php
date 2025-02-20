<?php

namespace App\Http\Controllers\AdminUser;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteUser;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary()
    {
        // Hitung total penjualan (jumlah seluruh total_amount pada orders)
        $totalSales = Order::sum('total_amount');

        // Hitung total pesanan
        $totalOrders = Order::count();

        // Hitung total pengguna (site_users)
        $totalUsers = SiteUser::count();

        // Hitung total produk
        $totalProducts = Product::count();

        return response()->json([
            'totalSales'    => $totalSales,
            'totalOrders'   => $totalOrders,
            'totalUsers'    => $totalUsers,
            'totalProducts' => $totalProducts,
        ], 200);
    }

    public function ordersData()
    {
        $orders = Order::selectRaw('MONTH(created_at) as month, COUNT(*) as Orders')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthNames = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec',
        ];

        $data = $orders->map(function ($item) use ($monthNames) {
            return [
                'name' => $monthNames[$item->month] ?? $item->month,
                'Orders' => (int)$item->Orders,
            ];
        });

        return response()->json($data, 200);
    }

    public function salesData()
    {
        $sales = Order::selectRaw('MONTH(created_at) as month, SUM(total_amount) as Sales')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthNames = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec',
        ];

        $data = $sales->map(function ($item) use ($monthNames) {
            return [
                'name' => $monthNames[$item->month] ?? $item->month,
                'Sales' => (int)$item->Sales,
            ];
        });

        return response()->json($data, 200);
    }

    public function recentOrders()
    {
        $orders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $data = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer' => $order->user ? $order->user->name : 'N/A',
                'total' => $order->total_amount,
                'status' => $order->status,
                'date' => $order->created_at->format('Y-m-d'),
            ];
        });

        return response()->json($data, 200);
    }
}
