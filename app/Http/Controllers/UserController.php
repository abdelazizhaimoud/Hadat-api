<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function update(Request $request){
        $user = $request->user();
        $validated = $request->validate([
            "name" => "nullable|string|min:2",
            "email" => "nullable|string|email",
            "bio" => "nullable|string|min:8",
            "avatar" => "nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048"
        ]);
        if($request->hasFile('avatar')){
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
        }

        $user->update($validated);
        return response()->json([
            "status" => "success",
            "user" => $user
        ]);
    }
}
