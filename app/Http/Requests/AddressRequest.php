<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'recipient_name' => 'required|string|max:50',
            'phone_number' => 'required|string|max:15',
            'address_line1'  => 'required|string|max:100',
            'address_line2'  => 'nullable|string|max:50',
            'province'       => 'required|string|max:50',
            'city'           => 'required|string|max:50',
            'postal_code'    => 'required|string|max:10',
            'is_default'     => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'phone_number.required' => 'Nomor telepon wajib diisi.',
            'address_line1.required'  => 'Alamat baris 1 wajib diisi.',
            'province.required'       => 'Provinsi wajib diisi.',
            'city.required'           => 'Kota wajib diisi.',
            'postal_code.required'    => 'Kode pos wajib diisi.',
        ];
    }
}
