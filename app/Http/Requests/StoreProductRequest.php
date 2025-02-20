<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function rules()
    {
        return [
            'product_name'   => 'required|string|max:50',
            'category_id'    => 'required|exists:categories,id',
            'original_price' => 'required|integer|min:0',
            'sale_price'     => 'nullable|integer|min:0',
            'size'           => 'required|string|max:20',
            'stock'          => 'required|integer|min:0',
            'weight'         => 'required|numeric|min:0',
            'description'    => 'nullable|string',
            'images.*'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'product_name.required'   => 'Nama produk wajib diisi.',
            'category_id.required'    => 'Kategori wajib dipilih.',
            'category_id.exists'      => 'Kategori tidak ditemukan.',
            'original_price.required' => 'Harga asli produk wajib diisi.',
            'size.required'           => 'Ukuran produk wajib diisi.',
            'stock.required'          => 'Stok produk wajib diisi.',
            'weight.required'         => 'Berat produk wajib diisi.',
            'images.*.image'          => 'File yang diunggah harus berupa gambar.',
            'images.*.mimes'          => 'Format gambar harus jpeg, png, jpg, atau gif.',
            'images.*.max'            => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}


