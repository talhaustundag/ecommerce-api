<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Kullanıcı giriş ve kayıt işlemleri"
 * )
 */



class LoginController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Kullanıcı giriş yapar",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="test@example.com"),
     *             @OA\Property(property="password", type="string", example="12345678")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Giriş başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Giriş Başarılı."),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string", example="1|abc123token..."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="array",
     *                 @OA\Items(type="string")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Email veya şifre hatalı",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Kullanıcı email veya şifre yanlış.")
     *         )
     *     )
     * )
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
            'success' => true,
            'message' => ['Giriş Başarılı.'],
            'user' => $user,
            'token' => $token,
            'errors' => []
        ];

        return response($response, 201);
    }

    /**
     *Kullanıcı kayıt islemleri icin bu controller kullanilacaktir.
     */
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Yeni kullanıcı oluşturur",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="Talha"),
     *             @OA\Property(property="email", type="string", example="talha@example.com"),
     *             @OA\Property(property="password", type="string", example="12345678")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Kayıt başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Kullanıcı Kaydı Başarılı."),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="array",
     *                 @OA\Items(type="string")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validasyon hatası"
     *     )
     * )
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
                'success' => true,
                'message' => ['Kullanıcı Kaydı Başarılı.'],
                'user' => $user,
                'token' => $token,
                'errors' => []
            ], 201);
        } else {
            return response([
                'Result' => 'Operation Failed'
            ], 401);
        }
    }

}

