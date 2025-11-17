<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Admin",
 *     description="Admin dashboard işlemleri"
 * )
 */

class AdminController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/dashboard",
     *     summary="Admin Dashboard verilerini getirir",
     *     tags={"Admin"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard verileri başarıyla getirildi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Admin Dashboard verileri getirildi."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="toplam_kullanici", type="integer", example=42),
     *                 @OA\Property(property="toplam_siparis", type="integer", example=128),
     *                 @OA\Property(property="toplam_gelir", type="number", format="float", example=15432.90),
     *                 @OA\Property(property="bugunku_siparis_sayisi", type="integer", example=6),
     *                 @OA\Property(
     *                     property="en_cok_satan_urunler",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="product_id", type="integer", example=3),
     *                         @OA\Property(property="total_quantity", type="integer", example=12),
     *                         @OA\Property(
     *                             property="product",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=3),
     *                             @OA\Property(property="name", type="string", example="iPhone 15 Pro"),
     *                             @OA\Property(property="price", type="number", example=49999)
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Yetkisiz erişim"
     *     )
     * )
     */
    public function dashboard()
    {
        $totalUsers = User::count();
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('total_amount');

        $ordersToday = Order::whereDate('created_at', Carbon::today())->count();

        $bestSellers = OrderItem::selectRaw('product_id, SUM(quantity) as total_quantity')
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Admin Dashboard verileri getirildi.',
            'data' => [
                'toplam_kullanici' => $totalUsers,
                'toplam_siparis' => $totalOrders,
                'toplam_gelir' => $totalRevenue,
                'bugunku_siparis_sayisi' => $ordersToday,
                'en_cok_satan_urunler' => $bestSellers
            ],
            'errors' => []
        ], 200);
    }
}
