<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $addresses = $user->addresses;

        return response()->json($addresses, 200);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'recipient_name' => 'required|string|max:50',
            'address_line1' => 'required|string|max:100',
            'address_line2' => 'nullable|string|max:50',
            'province' => 'required|string|max:50',
            'city' => 'required|string|max:50',
            'postal_code' => 'required|string|max:10',
            'is_default' => 'boolean',
        ], [
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'address_line1.required' => 'Alamat baris 1 wajib diisi.',
            'province.required' => 'Provinsi wajib diisi.',
            'city.required' => 'Kota wajib diisi.',
            'postal_code.required' => 'Kode pos wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Konversi is_default menjadi boolean yang valid
        $is_default = filter_var($request->input('is_default', false), FILTER_VALIDATE_BOOLEAN);

        // Jika alamat dijadikan default, ubah alamat lain menjadi non-default
        if ($is_default) {
            $user->addresses()->update(['is_default' => false]);
        }

        // Buat alamat baru terkait dengan pengguna
        $address = $user->addresses()->create([
            'recipient_name' => $request->recipient_name,
            'address_line1' => $request->address_line1,
            'address_line2' => $request->address_line2,
            'province' => $request->province,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'is_default' => $is_default,
        ]);

        return response()->json([
            'message' => 'Alamat berhasil ditambahkan.',
            'address' => $address,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();

        // Cari alamat milik pengguna
        $address = $user->addresses()->where('id', $id)->first();

        if (!$address) {
            return response()->json(['message' => 'Alamat tidak ditemukan.'], 404);
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'recipient_name' => 'required|string|max:50',
            'address_line1' => 'required|string|max:100',
            'address_line2' => 'nullable|string|max:50',
            'province' => 'required|string|max:50',
            'city' => 'required|string|max:50',
            'postal_code' => 'required|string|max:10',
            'is_default' => 'boolean',
        ], [
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'address_line1.required' => 'Alamat baris 1 wajib diisi.',
            'province.required' => 'Provinsi wajib diisi.',
            'city.required' => 'Kota wajib diisi.',
            'postal_code.required' => 'Kode pos wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Konversi is_default menjadi boolean yang valid
        $is_default = filter_var($request->input('is_default', false), FILTER_VALIDATE_BOOLEAN);

        if ($is_default) {
            // Ubah alamat lain menjadi non-default
            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        // Update data alamat
        $address->update([
            'recipient_name' => $request->recipient_name,
            'address_line1' => $request->address_line1,
            'address_line2' => $request->address_line2,
            'province' => $request->province,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'is_default' => $is_default,
        ]);

        return response()->json([
            'message' => 'Alamat berhasil diperbarui.',
            'address' => $address,
        ], 200);
    }


    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        // Cari alamat milik pengguna
        $address = $user->addresses()->where('id', $id)->first();

        if (!$address) {
            return response()->json([
                'message' => 'Alamat tidak ditemukan.'
            ], 404);
        }

        // Hapus alamat
        $address->delete();

        return response()->json(['message' => 'Alamat berhasil dihapus.'], 200);
    }


    public function show(Request $request, $id)
    {
        $user = $request->user();

        $address = $user->addresses()->where('addresses.id', $id)->first();

        if (!$address) {
            return response()->json([
                'message' => 'Alamat tidak ditemukan.'
            ], 404);
        }

        return response()->json($address, 200);
    }
}
