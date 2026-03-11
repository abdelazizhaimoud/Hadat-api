<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
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
                "token" => $token
            ]);
            
    }
    public function login(Request $request){
        $validated = $request->validate([
            "email" => "required|string|min:8|max:255|email",
            "password" => "required|string|min:8|max:255"
        ]);

        $user = User::where('email',$validated["email"])->get();
        return response()->json([
            "user" => $user
        ]);
    }
}
