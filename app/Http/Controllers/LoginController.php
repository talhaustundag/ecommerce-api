<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     *Login islemleri icin bu controller kullanilacaktir.
     */
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => ['Kullanıcı email veya şifre yanlış.']
            ], 404);
        }

        $token = $user->createToken('my-app-token')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token,
            'message' => ['Giriş Başarılı.']
        ];

        return response($response, 201);
    }

    /**
     *Kullanıcı kayıt islemleri icin bu controller kullanilacaktir.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = 0;
        $result = $user->save();

        $token = $user->createToken('my-app-token')->plainTextToken;

        if ($result) {
            return response([
                'user' => $user,
                'token' => $token
            ], 201);
        } else {
            return response([
                'Result' => 'Operation Failed'
            ], 401);
        }
    }

}

