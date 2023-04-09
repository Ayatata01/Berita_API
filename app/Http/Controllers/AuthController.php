<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Successfully registered user'
        ], 201);
    }

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!auth()->attempt($validatedData)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = auth()->user();
        $token = $user->createToken('authToken')->plainTextToken;

        date_default_timezone_set('Asia/Jakarta');
        $datetime = date("h:i:sa");
        $waktu = explode(":", $datetime);
        $h = $waktu[0] + 2;
        $m = $waktu[1];
        $s = $waktu[2];

        return response()->json([
            'token' => $token,
            'expires_in' => $h . ":" . $m . ":" . $s
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function refreshToken(Request $request)
    {
        $user = $request->user();
        $user->tokens()->where('id', $request->input('token_id'))->delete();

        $token = $user->createToken('API Token')->plainTextToken;

        date_default_timezone_set('Asia/Jakarta');
        $datetime = date("h:i:sa");
        $waktu = explode(":", $datetime);
        $h = $waktu[0] + 2;
        $m = $waktu[1];
        $s = $waktu[2];

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $h . ":" . $m . ":" . $s
        ], 200);
    }
}
