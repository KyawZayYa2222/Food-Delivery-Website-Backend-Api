<?php

namespace App\Http\Controllers\auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Register
    public function store(Request $request) {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);

        $user = User::create([
            'name'=> $fields['name'],
            'email'=> $fields['email'],
            'password'=> Hash::make($fields['password']),
        ]);

        $token = $user->createToken('api-access-token')->plainTextToken;

        $userData = User::where('email', $fields['email'])->get()->first();

        return response()->json([
            'status' => 201,
            'message' => 'successfully registered',
            'userData' => $userData,
            'token' => $token,
        ], 201);
    }

    // Login
    public function login(Request $request) {
        $validators = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string',
        ]);

        if(Auth::attempt($validators)) {
            $user = auth()->user();

            $token = $user->createToken('api-access-token')->plainTextToken;

            return response()->json([
                'status' => 200,
                'message' => 'successfully logined',
                'userData' => $user,
                'token' => $token,
            ]);
        } else {
            return response()->json([
                'status' => 401,
                'message' => 'Invalid user email or password',
            ], 401);
        }
    }

    // Logout
    public function logout() {
        Auth()->user()->tokens()->delete();

        return response()->json([
            'status' => 200,
            'message' => 'successfully logout',
        ]);
    }
}
