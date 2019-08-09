<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function register(Request $request)
    {

        $validatedData = $request->validate([
            'email' => 'required|unique:users|max:100|email',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
            'name' => 'required',
            'address' => 'required',
            'phone' => 'numeric|min:11|required',
        ]);
        $validatedData['password'] = bcrypt($validatedData['password']);
        $user = new User;
        $user->create($validatedData);
        $token = $user->createToken('My Token', ['user'])->accessToken;

        return response()->json(['token' => $token], 201);
    }

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $validatedData['email'], 'password' => $validatedData['password']])) {
            $role = Auth::user()->role;
            $token = Auth::user()->createToken('My Token', [$role])->accessToken;
            return response()->json(['token' => $token], 200);
        } else {
            return response()->json(['status', 'Unauthorized'], 401);
        }
    }
}
