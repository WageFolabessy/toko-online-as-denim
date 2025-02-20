<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalculateShippingCostRequest extends FormRequest
{

    public function rules()
    {
        return [
            'destination'     => 'required|string',
            'weight'          => 'required|integer|min:1',
            'courier'         => 'required|string',
            'price'           => 'nullable|string|in:lowest,highest',
        ];
    }

    public function messages()
    {
        return [
            'destination.required' => 'Tujuan pengiriman wajib diisi.',
            'weight.required'      => 'Berat paket wajib diisi.',
            'courier.required'     => 'Kurir wajib dipilih.',
            'price.in'             => 'Opsi harga tidak valid.',
        ];
    }
}

