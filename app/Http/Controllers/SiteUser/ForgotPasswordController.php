<?php

namespace App\Http\Controllers\SiteUser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SiteUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = SiteUser::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email tidak ditemukan.'], 404);
        }

        $token = Str::random(60);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            [
                'token'      => $token,
                'created_at' => Carbon::now()
            ]
        );

        $resetLink = 'http://localhost:5173' . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);

        Mail::send('email.reset', ['resetLink' => $resetLink, 'user' => $user], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Reset Password Notification');
        });

        return response()->json(['message' => 'Link reset password telah dikirim ke email Anda.']);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email',
            'token'                 => 'required',
            'password'              => 'required|confirmed|min:8',
        ]);

        $passwordReset = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$passwordReset) {
            return response()->json(['message' => 'Token atau email tidak valid.'], 400);
        }

        if (Carbon::parse($passwordReset->created_at)->addMinutes(60)->isPast()) {
            return response()->json(['message' => 'Token sudah kadaluarsa.'], 400);
        }

        $user = SiteUser::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Email tidak ditemukan.'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password berhasil diubah.']);
    }
}
