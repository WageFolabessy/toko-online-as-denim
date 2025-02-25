<?php

namespace App\Http\Controllers\AdminUser;

use App\Http\Controllers\Controller;
use App\Models\SiteUser;
use Illuminate\Http\Request;

class SiteUserDetailController extends Controller
{
    public function index()
    {
        $admins = SiteUser::orderBy('created_at', 'desc')->get();
        return response()->json($admins, 200);
    }

    public function show($id)
    {
        $user = SiteUser::with([
            'addresses',
            'orders.orderItems'
        ])->find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Pengguna tidak ditemukan.'
            ], 404);
        }

        return response()->json($user, 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $user = SiteUser::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Pengguna tidak ditemukan.'
            ], 404);
        }

        // Validasi input status
        $data = $request->validate([
            'is_active' => 'required|boolean'
        ], [
            'is_active.required' => 'Status akun wajib diisi.',
            'is_active.boolean'  => 'Status akun harus bernilai true atau false.',
        ]);

        // Perbarui status akun
        $user->update($data);

        return response()->json([
            'message' => 'Status akun pengguna berhasil diperbarui.',
            'user'    => $user
        ], 200);
    }
}
