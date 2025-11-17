<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="User",
 *     description="Kullanıcı profil işlemleri"
 * )
 */

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/user/profile",
     *     summary="Kullanıcı profilini getir",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Kullanıcı profili getirildi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kullanıcı Profili"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Talha Üstündağ"),
     *                 @OA\Property(property="email", type="string", example="talha@example.com")
     *             ),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/user/update",
     *     summary="Kullanıcı profilini güncelle",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Yeni İsim")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Profil güncellendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profil güncellendi."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Yeni İsim"),
     *                 @OA\Property(property="email", type="string", example="talha@example.com")
     *             ),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validasyon hatası"
     *     )
     * )
     */
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
