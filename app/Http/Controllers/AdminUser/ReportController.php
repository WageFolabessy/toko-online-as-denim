<?php

namespace App\Http\Controllers\AdminUser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('orders as o')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->leftJoin('payments as pay', 'o.id', '=', 'pay.order_id')
            ->leftJoin('shipments as s', 'o.id', '=', 's.order_id')
            ->select(
                'o.order_number',
                'o.created_at',
                'o.total_amount',
                'oi.qty',
                'oi.price',
                'p.product_name',
                'pay.status as payment_status',
                's.tracking_number'
            )
            ->whereIn('o.status', ['paid', 'processing', 'shipped', 'delivered']);

        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $query->whereBetween('o.created_at', [$startDate, $endDate]);
        }

        $salesReports = $query->orderBy('o.created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $salesReports
        ]);
    }
}
