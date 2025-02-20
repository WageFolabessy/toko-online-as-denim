<?php

namespace App\Http\Controllers\SiteUser;

use App\Http\Requests\CalculateShippingCostRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class ShipmentController extends Controller
{
    public function calculateShippingCost(CalculateShippingCostRequest $request)
    {
        try {
            // Validasi input dari body permintaan
            $validatedData = $request->validated();

            // Tambahkan 'origin' dari env atau konfigurasi Anda
            $validatedData['origin'] = env('POSTAL_CODE_ORIGIN');

            // $apiKey = env('RAJA_ONGKIR_API_KEY');
            $apiKey = env('RAJA_ONGKIR_API_KEY_FOLABESSY26');

            // Mengirim permintaan POST dengan query parameters
            $response = Http::withHeaders([
                'key' => $apiKey,
                'Accept' => 'application/json',
            ])
                ->withOptions([
                    'query' => $validatedData,
                ])
                ->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost');

            // Log data yang dikirim
            Log::info('Data dikirim ke RajaOngkir:', $validatedData);

            if ($response->successful() && isset($response->json()['data'])) {
                return response()->json($response->json()['data'], 200);
            } else {
                return response()->json([
                    'message' => 'Gagal menghitung ongkos kirim.',
                    'error'   => $response->json()['meta']['message'] ?? 'Unknown error',
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Error menghitung ongkos kirim:', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat menghitung ongkos kirim.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
