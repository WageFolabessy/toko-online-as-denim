<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{

    public function rules()
    {
        return [
            'qty' => 'required|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'qty.required' => 'Jumlah produk wajib diisi.',
            'qty.integer'  => 'Jumlah produk harus berupa angka.',
            'qty.min'      => 'Jumlah produk minimal 1.',
        ];
    }
}

