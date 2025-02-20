<?php

namespace App\Http\Controllers\SiteUser;

use App\Http\Requests\AddressRequest;
use App\Models\Address;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AddressController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Address::class, 'address');
        $this->middleware('auth:sanctum');
    }
    
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses;

        return response()->json($addresses, 200);
    }

    public function store(AddressRequest $request)
    {
        $user = $request->user();

        // If address is set as default, unset previous default addresses
        if ($request->is_default) {
            $user->addresses()->update(['is_default' => false]);
        }

        // Create new address
        $address = $user->addresses()->create($request->validated());

        return response()->json([
            'message' => 'Alamat berhasil ditambahkan.',
            'address' => $address,
        ], 201);
    }

    public function show(Address $address)
    {
        return response()->json($address, 200);
    }

    public function update(AddressRequest $request, Address $address)
    {
        // If address is set as default, unset previous default addresses
        if ($request->is_default) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        // Update address
        $address->update($request->validated());

        return response()->json([
            'message' => 'Alamat berhasil diperbarui.',
            'address' => $address,
        ], 200);
    }
    
    public function destroy(Address $address)
    {
        $address->delete();

        return response()->json(['message' => 'Alamat berhasil dihapus.'], 200);
    }
}
