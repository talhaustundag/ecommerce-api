<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmationMail;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Order",
 *     description="Sipariş yönetimi işlemleri"
 * )
 */

class OrderController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Yeni sipariş oluştur",
     *     tags={"Order"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=201,
     *         description="Sipariş başarıyla oluşturuldu",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sipariş başarıyla oluşturuldu."),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Sepet boş veya stok yetersiz",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Sepet boş."),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function createOrder()
    {
        $cart = Cart::where('user_id', auth()->id())
            ->with('items.product')
            ->first();

        if (!$cart || $cart->items->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Sepet boş.',
                'data' => null,
                'errors' => []
            ], 400);
        }

        DB::beginTransaction();
        try {

            $total = 0;

            // TÜM ÜRÜNLERİN STOK KONTROLÜ — TEK SEFERDE
            foreach ($cart->items as $item) {
                if ($item->product->stock < $item->quantity) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => $item->product->name . ' için yeterli stok yok.',
                        'data' => null,
                        'errors' => []
                    ], 400);
                }

                $total += $item->product->price * $item->quantity;
            }

            // SİPARİŞ OLUŞTURMA
            $order = Order::create([
                'user_id' => auth()->id(),
                'total_amount' => $total,
                'status' => 'beklemede'
            ]);

            // ORDER ITEMS OLUŞTURMA + STOK DÜŞME
            foreach ($cart->items as $item) {

                // STOK DÜŞME
                $item->product->stock -= $item->quantity;
                $item->product->save();

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price
                ]);
            }

            // SEPETİ TEMİZLEME
            $cart->items()->delete();

            DB::commit();
            Mail::to($order->user->email)->send(new OrderConfirmationMail($order));

            return response()->json([
                'success' => true,
                'message' => 'Sipariş başarıyla oluşturuldu.',
                'data' => $order->load('items.product'),
                'errors' => []
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Sipariş oluşturulurken bir hata oluştu.',
                'data' => null,
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Kullanıcının tüm siparişlerini listele",
     *     tags={"Order"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Sipariş listesi getirildi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="orders", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    //SİPARİŞ LİSTELEME
    public function listOrders()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with('items.product')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $orders,
            'errors' => []
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/orders/{id}/status",
     *     summary="Sipariş durumunu güncelle (Admin)",
     *     tags={"Order"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="hazırlanıyor")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Sipariş durumu güncellendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sipariş durumu güncellendi."),
     *             @OA\Property(property="order", type="object"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Geçersiz sipariş durumu"
     *     )
     * )
     */
    //SİPARİŞ GÜNCELLEME
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(
            [
                'status' => 'required|in:beklemede,hazırlanıyor,kargolandı,teslim_edildi,iptal'
            ],
            [
                'status.in' => 'Seçilen sipariş durumu geçersiz. Geçerli durumlar: beklemede, hazırlanıyor, kargolandı, teslim edildi, iptal.'
            ]
        );

        $order->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sipariş durumu güncellendi.',
            'order' => $order,
            'errors' => []
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     summary="Sipariş detayını getir",
     *     tags={"Order"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Sipariş detayı getirildi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sipariş detayı getirildi."),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Sipariş bulunamadı"
     *     )
     * )
     */
    public function detailOrders($order_id)
    {
        $order = Order::where('id', $order_id)
            ->where('user_id', auth()->id())
            ->with(['items.product', 'user'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Sipariş bulunamadı.',
                'data' => null,
                'errors' => []
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sipariş detayı getirildi.',
            'data' => [
                'order' => [
                    'id' => $order->id,
                    'user' => [
                        'id' => $order->user->id,
                        'name' => $order->user->name,
                        'email' => $order->user->email,
                    ],
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,

                    // Ürünler
                    'items' => $order->items->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'total' => $item->price * $item->quantity
                        ];
                    })
                ]
            ],
            'errors' => []
        ], 200);
    }

}
