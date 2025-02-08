<?php

namespace App\Http\Controllers;

use App\Models\SiteUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'username' => 'required|string|max:20|unique:site_users,username',
            'phone_number' => 'required|string|max:15',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama tidak boleh lebih dari 50 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.max' => 'Username tidak boleh lebih dari 20 karakter.',
            'username.unique' => 'Username sudah terdaftar, gunakan username lain.',
            'phone_number.required' => 'Nomor telepon wajib diisi.',
            'phone_number.max' => 'Nomor telepon tidak boleh lebih dari 15 karakter.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password harus minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = new SiteUser();
        $user->name = $request->name;
        $user->username = $request->username;
        $user->phone_number = $request->phone_number;
        $user->password = Hash::make($request->password);
        $user->save();

        $token = $user->createToken($request->username);

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'user' => $user,
            'token' => $token->plainTextToken
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = SiteUser::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Username atau password salah.'
            ], 401);
        }

        $token = $user->createToken($user->username);

        return response()->json([
            'message' => 'Login berhasil.',
            'user' => $user,
            'token' => $token->plainTextToken
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();

        return response()->json([
            'message' => 'Logout berhasil.'
        ], 200);
    }

    public function getUser(Request $request)
    {
        $user = $request->user();

        $user->makeHidden(['password', 'remember_token']);

        return response()->json($user, 200);
    }

    public function updateUser(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'phone_number' => 'required|string|max:15',
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama tidak boleh lebih dari 50 karakter.',
            'phone_number.required' => 'Nomor telepon wajib diisi.',
            'phone_number.max' => 'Nomor telepon tidak boleh lebih dari 15 karakter.',
            'password.min' => 'Password harus minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user->name = $request->name;
        $user->phone_number = $request->phone_number;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => $user->makeHidden(['password', 'remember_token']),
        ], 200);
    }
}
