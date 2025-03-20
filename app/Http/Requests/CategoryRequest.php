<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function rules()
    {
        $categoryId = $this->route('category');

        return [
            'category_name' => 'required|max:50|unique:categories,category_name' . $categoryId,
            'image' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'image',
                'mimes:jpeg,jpg,png',
                'max:2048',
            ],
        ];
    }

    public function messages()
    {
        return [
            'category_name.required' => 'Nama kategori wajib diisi.',
            'category_name.unique'   => 'Nama kategori sudah terdaftar.',
            'image.required'         => 'Gambar kategori wajib isi.',
            'image.image'            => 'File yang diunggah harus berupa gambar.',
            'image.max'              => 'Ukuran gambar terlalu besar. Maksimal 2MB.',
            'image.mimes'              => 'Format gambar harus jpeg, png, atau webp.',
        ];
    }
}

