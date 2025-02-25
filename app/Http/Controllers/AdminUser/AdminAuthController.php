<?php

namespace App\Http\Controllers\AdminUser;

use App\Http\Requests\UpdateAdminUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\AdminStoreRequest;
use App\Models\AdminUser;

class AdminAuthController extends Controller
{
    public function index()
    {
        $admins = AdminUser::orderBy('created_at', 'desc')->get();
        return response()->json($admins, 200);
    }

    public function store(AdminStoreRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        AdminUser::create($data);

        return response()->json([
            'message' => 'Admin baru berhasil dibuat.',
        ], 201);
    }

    public function show(Request $request)
    {
        $admin = $request->user()->makeHidden(['password', 'remember_token']);

        return response()->json($admin, 200);
    }

    public function update(UpdateAdminUserRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user'    => $user->makeHidden(['password', 'remember_token']),
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        // Cari admin berdasarkan ID
        $admin = AdminUser::find($id);

        if (!$admin) {
            return response()->json([
                'message' => 'Admin tidak ditemukan.'
            ], 404);
        }

        // Cek apakah admin yang ingin dihapus adalah admin yang sedang login
        if ($request->user()->id == $admin->id) {
            return response()->json([
                'message' => 'Anda tidak dapat menghapus akun sendiri.'
            ], 403);
        }

        // Hapus admin
        $admin->delete();

        return response()->json([
            'message' => 'Admin berhasil dihapus.'
        ], 200);
    }

    public function showSelectedAdmin(Request $request, $id)
    {
        $admin = AdminUser::find($id);
        if (!$admin) {
            return response()->json([
                'message' => 'Admin tidak ditemukan.',
            ], 404);
        }

        return response()->json($admin, 200);
    }

    public function updateSelectedAdmin(Request $request, $id)
    {
        // Cari admin berdasarkan ID
        $admin = AdminUser::find($id);

        if (!$admin) {
            return response()->json([
                'message' => 'Admin tidak ditemukan.'
            ], 404);
        }

        // Validasi input yang diterima
        $validatedData = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email,' . $admin->id,
        ], [
            'name.required'         => 'Nama wajib diisi.',
            'name.max'              => 'Nama tidak boleh lebih dari 50 karakter.',
            'email.required'        => 'email wajib diisi.',
            'email.unique'          => 'email sudah terdaftar, gunakan email lain.',
        ]);

        // Update data admin
        $admin->update($validatedData);

        // Kembalikan response dengan data admin yang sudah diperbarui
        return response()->json($admin, 200);
    }

    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->validated();

        $user = AdminUser::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $token = $user->createToken($user->email)->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'user'    => $user->makeHidden(['password', 'remember_token']),
            'token'   => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ], 200);
    }
}
