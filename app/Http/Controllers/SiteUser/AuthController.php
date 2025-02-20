<?php

namespace App\Http\Controllers\SiteUser;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateSiteUserRequest;
use App\Models\SiteUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = SiteUser::create($data);


        return response()->json([
            'message' => 'Registrasi berhasil.',
            'user'    => $user->makeHidden(['password', 'remember_token']),
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        $user = SiteUser::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Akun Anda telah dinonaktifkan. Silakan hubungi admin.',
            ], 403);
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

    public function getUser(Request $request)
    {
        $user = $request->user()->makeHidden(['password', 'remember_token']);

        return response()->json($user, 200);
    }

    public function updateUser(UpdateSiteUserRequest $request)
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
}
