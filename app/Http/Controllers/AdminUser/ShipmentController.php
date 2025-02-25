<?php

namespace App\Http\Controllers\AdminUser;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function index()
    {
        $shipments = Shipment::with('order.user')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'shipments' => $shipments,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'status'         => 'required|string|in:pending,shipped,delivered',
            'tracking_number'=> 'nullable|string'
        ], [
            'status.required' => 'Status pengiriman harus diisi.',
            'status.in'       => 'Status pengiriman tidak valid.',
        ]);

        $shipment = Shipment::find($id);
        if (!$shipment) {
            return response()->json([
                'message' => 'Pengiriman tidak ditemukan.'
            ], 404);
        }

        $shipment->update($data);

        return response()->json([
            'message'  => 'Pengiriman berhasil diperbarui.',
            'shipment' => $shipment,
        ], 200);
    }
}
