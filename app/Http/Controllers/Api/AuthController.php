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
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'address' => 'required',
            'phone' => 'numeric|min:11|required|unique:users',
            'disease' => 'nullable',
            'dob' => 'date_format:"Y-m-d"|required',
            'gender' => 'required',
            'photo' => 'nullable'
        ]);
        $validatedData['password'] = bcrypt($validatedData['password']);
        $user = new User;
        $validatedData['active'] = '1';
        $user->create($validatedData);
        $token = $user->createToken('My Token', ['user'])->accessToken;

        return response()->json(['status'=>true,'token' => $token], 201);
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
            return response()->json(['status'=>true,'token' => $token], 200);
        } else {
            return response()->json(['status'=> false], 401);
        }
    }
}
