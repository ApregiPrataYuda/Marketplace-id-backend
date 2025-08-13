<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Users;
use App\Http\Requests\RegistrationValidated;

class AuthController extends Controller
{
     public function register(RegistrationValidated $request)
    {
        try {
            $user = Users::create([
                'name' => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Register berhasil',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat registrasi',
                'error' => config('app.debug') ? $e->getMessage() : 'Silakan coba lagi nanti.',
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = Users::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        // Hapus token yang sedang digunakan
        $user->currentAccessToken()->delete();

        if ($user->tokens()->count() === 0) {
            return response()->json([
                'message' => 'Logout berhasil dan semua token sudah dihapus'
            ]);
        }

        return response()->json([
            'message' => 'Logout berhasil, tetapi masih ada token lain yang aktif'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
