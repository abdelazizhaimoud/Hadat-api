<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function signup(Request $request){
        $validated = $request->validate([
            "email" => "required|string|min:8|max:255|email",
            "name" => "nullable|string|min:8|max:255",
            "password" => "required|string|min:8|max:255",
        ]);
        $user = User::create($validated);
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            "status" => "success",
            "token" => $token,
            "user" => $user
        ],201);
    }
    public function login(Request $request){
        $validated = $request->validate([
            "email" => "required|string|min:8|max:255|email",
            "password" => "required|string|min:8|max:255"
        ]);

        if (Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])){
            $user = User::where('email',$validated["email"])->first();
            $token = $user->createToken('auth-token')->plainTextToken;
            return response()->json([
                "status" => "success",
                "token" => $token,
                "user" => $user
            ],200);
        }

        return response()->json([
            "status" => "error",
            "message" => "wrong credentials !"
        ],401);
            
    }
}
