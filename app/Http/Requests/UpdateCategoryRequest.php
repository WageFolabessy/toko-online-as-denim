<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function rules()
    {
        return [
            'category_name' => [
                'required',
                Rule::unique('categories', 'category_name')->ignore($this->route('category')),
            ],
            'image' => 'nullable|mimes:jpeg,jpg,png|max:2028',
        ];
    }

    public function messages()
    {
        return [
            'category_name.required' => 'Nama kategori wajib diisi.',
            'category_name.unique' => 'Nama kategori sudah terdaftar.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.max' => 'Ukuran gambar terlalu besar. Maksimal 2MB.',
            'image.mimes'              => 'Format gambar harus jpeg, png, atau webp.',
        ];
    }
}