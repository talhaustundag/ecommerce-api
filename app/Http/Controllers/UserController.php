<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    // Profil
    public function profile()
    {
        $user = User::where('id', Auth::id())->first();

        return response()->json([
            'success' => true,
            'message' => 'Kullanıcı Profili',
            'data' => $user,
            'errors' => []
        ], 200);
    }

    // Profil Güncelleme
    public function update(UserRequest $request)
    {
        $user = Auth::user();
        $user->update([
            'name' => $request->name
        ]);
        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Profil güncellendi.',
            'data' => $user,
            'errors' => []
        ], 200);
    }
}
