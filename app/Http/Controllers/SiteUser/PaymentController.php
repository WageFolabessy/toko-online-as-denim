<?php

namespace App\Http\Controllers\SiteUser;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ShoppingCartItem;
use Midtrans\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    private function initMidtrans()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
        Config::$appendNotifUrl = env('NGROK_HTTP_8000');
    }

    public function initiatePayment(Request $request)
    {
        $this->initMidtrans();
        $cartItems = $request->input('cartItems');
        $addressId = $request->input('address_id');
        $shippingOption = $request->input('shipping_option');
        $user = $request->user();

        // Validasi data
        if (!$cartItems || count($cartItems) === 0) {
            return response()->json(['error' => 'Keranjang belanja kosong.'], 400);
        }
        if (!$addressId) {
            return response()->json(['error' => 'Alamat pengiriman tidak tersedia.'], 400);
        }
        if (!$shippingOption) {
            return response()->json(['error' => 'Metode pengiriman tidak dipilih.'], 400);
        }

        DB::beginTransaction(); // Mulai transaksi database

        try {
            $totalAmount = 0;
            $orderItems = [];
            $itemDetails = [];

            foreach ($cartItems as $item) {
                $product = Product::find($item['product_id']);

                if (!$product) {
                    throw new \Exception('Produk dengan ID ' . $item['product_id'] . ' tidak ditemukan.', 404);
                }

                // Periksa ketersediaan stok
                if ($product->stock < $item['qty']) {
                    throw new \Exception("Stok produk {$product->product_name} tidak mencukupi.", 400);
                }

                $price = $product->sale_price ?? $product->original_price;
                $subtotal = $price * $item['qty'];
                $totalAmount += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];

                $itemDetails[] = [
                    'id' => (string)$product->id,
                    'price' => $price,
                    'quantity' => $item['qty'],
                    'name' => substr($product->product_name, 0, 50),
                ];

                // Kurangi stok produk
                $product->decrement('stock', $item['qty']);
            }

            $shippingCost = $shippingOption['cost'];
            $totalAmount += $shippingCost;

            $itemDetails[] = [
                'id' => 'SHIPPING',
                'price' => $shippingCost,
                'quantity' => 1,
                'name' => 'Ongkos Kirim',
            ];

            $orderNumber = 'ORDER-' . time() . '-' . $user->id;

            // Buat pesanan
            $order = Order::create([
                'site_user_id' => $user->id,
                'address_id' => $addressId,
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
                'shipping_cost' => $shippingCost,
                'status' => 'pending',
            ]);

            // Simpan item pesanan
            foreach ($orderItems as $orderItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $orderItem['product_id'],
                    'qty' => $orderItem['qty'],
                    'price' => $orderItem['price'],
                ]);
            }

            // Simpan pengiriman
            Shipment::create([
                'order_id' => $order->id,
                'courier' => $shippingOption['code'],
                'service' => $shippingOption['service'],
                'tracking_number' => null,
                'shipping_cost' => $shippingCost,
                'status' => 'pending',
            ]);

            // Hapus item keranjang belanja pengguna
            ShoppingCartItem::whereHas('shoppingCart', function ($query) use ($user) {
                $query->where('site_user_id', $user->id);
            })->delete();

            DB::commit(); // Komit transaksi database

            // Ambil token pembayaran dari Midtrans
            $params = [
                'transaction_details' => [
                    'order_id' => $orderNumber,
                    'gross_amount' => $totalAmount,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone_number,
                ],
                'item_details' => $itemDetails,
            ];

            $snapToken = Snap::getSnapToken($params);

            return response()->json(['snapToken' => $snapToken]);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaksi jika terjadi error

            $statusCode = $e->getCode() ?: 500;
            return response()->json(['error' => $e->getMessage()], $statusCode);
        }
    }

    public function handleNotification(Request $request)
    {
        Log::info('Notifikasi diterima dari Midtrans:', $request->all());
        $this->initMidtrans();

        $notification = new Notification();

        $order = Order::where('order_number', $notification->order_id)->first();

        if (!$order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        $transactionStatus = $notification->transaction_status;
        $paymentType = $notification->payment_type;
        $fraudStatus = $notification->fraud_status;
        $transactionId = $notification->transaction_id;
        $grossAmount = $notification->gross_amount;

        // Simpan status sebelumnya
        $previousStatus = $order->status;

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                $order->status = 'paid';
            } else if ($fraudStatus == 'challenge') {
                $order->status = 'pending';
            } else {
                $order->status = 'fraud';
            }
        } else if ($transactionStatus == 'settlement') {
            $order->status = 'paid';
        } else if ($transactionStatus == 'pending') {
            $order->status = 'pending';
        } else if ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
            $order->status = 'failed';
        }

        $order->save();

        // Simpan data pembayaran
        Payment::updateOrCreate(
            ['transaction_id' => $transactionId],
            [
                'order_id' => $order->id,
                'payment_type' => $paymentType,
                'status' => $transactionStatus,
                'amount' => $grossAmount,
                'metadata' => json_encode($notification),
            ]
        );

        // Jika pembayaran gagal dan status sebelumnya bukan 'failed', kembalikan stok
        if ($order->status == 'failed' && $previousStatus != 'failed') {
            foreach ($order->orderItems as $orderItem) {
                $product = Product::find($orderItem->product_id);
                if ($product) {
                    $product->increment('stock', $orderItem->qty);
                }
            }
        }

        Log::info('Notifikasi diproses untuk pesanan:', ['order_id' => $order->id]);
        return response()->json(['message' => 'Notifikasi diproses.']);
    }
}
