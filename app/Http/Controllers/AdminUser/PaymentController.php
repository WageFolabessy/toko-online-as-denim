<?php

namespace App\Http\Controllers\AdminUser;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with('order.user')->orderBy('created_at', 'desc')->get();
        return response()->json([
            'payments' => $payments,
        ], 200);
    }

    public function show($id)
    {
        $payment = Payment::with('order')->find($id);
        if (!$payment) {
            return response()->json([
                'message' => 'Pembayaran tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'payment' => $payment,
        ], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|string|in:settlement,pending,cancel,expired,deny',
        ], [
            'status.required' => 'Status pembayaran harus diisi.',
            'status.in'       => 'Status pembayaran tidak valid.',
        ]);

        $payment = Payment::find($id);
        if (!$payment) {
            return response()->json([
                'message' => 'Pembayaran tidak ditemukan.'
            ], 404);
        }

        // Update status pembayaran
        $payment->update([
            'status' => $data['status']
        ]);

        return response()->json([
            'message' => 'Status pembayaran berhasil diperbarui.',
            'payment' => $payment,
        ], 200);
    }
}
