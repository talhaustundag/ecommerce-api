<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Cart",
 *     description="Sepet yönetimi işlemleri"
 * )
 */

class CartController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/cart",
     *     summary="Kullanıcının sepetini getirir",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Sepet başarıyla getirildi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", nullable=true, example=null),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function getCart()
    {
        $cart = Cart::where('user_id', auth()->id())
            ->with(['items.product'])
            ->first();

        return response()->json([
            'success' => true,
            'data' => $cart
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/cart/add",
     *     summary="Sepete ürün ekler",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=2)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Ürün sepete eklendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ürün sepete eklendi."),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1'
        ]);

        $quantity = $request->quantity ?? 1;

        // Kullanıcının sepeti yoksa oluştur
        $cart = Cart::firstOrCreate([
            'user_id' => auth()->id()
        ]);

        // Ürün sepette var mı?
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'quantity' => $quantity
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ürün sepete eklendi.',
            'data' => $cartItem,
            'errors' => []
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/cart/remove/{product_id}",
     *     summary="Sepetten ürün çıkarır (1 adet azaltır)",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         required=true,
     *         description="Ürün ID",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Ürün sepetten çıkarıldı",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ürün sepetten çıkarıldı."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="product_id", type="integer", example=1),
     *                 @OA\Property(property="remaining_quantity", type="integer", example=0)
     *             ),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Sepet veya ürün bulunamadı"
     *     )
     * )
     */
    public function removeItem($product_id)
    {
        // Parametre doğrulama
        if (!Product::where('id', $product_id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz ürün ID.',
                'data' => null,
                'errors' => []
            ], 404);
        }

        $cart = Cart::where('user_id', auth()->id())->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Sepet bulunamadı.',
                'data' => null,
                'errors' => []
            ], 404);
        }

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product_id)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Ürün sepette yok.',
                'data' => null,
                'errors' => []
            ], 404);
        }

        if ($cartItem->quantity > 1) {
            $cartItem->quantity -= 1;
            $cartItem->save();
        } else {
            $cartItem->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Ürün sepetten çıkarıldı.',
            'data' => [
                'product_id' => $product_id,
                'remaining_quantity' => $cartItem->quantity ?? 0
            ],
            'errors' => []
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/cart/clear",
     *     summary="Sepeti tamamen temizler",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Sepet temizlendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sepet boşaltıldı."),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function clearCart()
    {
        $cart = Cart::where('user_id', auth()->id())->first();

        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Sepet boşaltıldı.',
            'data' => [],
            'errors' => []
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/cart/update",
     *     summary="Sepetteki ürün miktarını günceller",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id","quantity"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=3)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Miktar güncellendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ürün miktarı güncellendi."),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        // Kullanıcının sepeti var mı kontrolü
        $cart = Cart::where('user_id', auth()->id())->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Sepet bulunamadı.',
                'data' => null,
                'errors' => []
            ], 404);
        }

        // Ürün sepette var mı kontrolü
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->with('product') // product bilgisi için
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Ürün sepette bulunamadı.',
                'data' => null,
                'errors' => []
            ], 404);
        }

        // Stok kontrolü
        if ($cartItem->product->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Yeterli stok yok.',
                'data' => null,
                'errors' => []
            ], 400);
        }

        // Miktarı güncelleme
        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Ürün miktarı güncellendi.',
            'data' => $cartItem->load('product'),
            'errors' => []
        ], 200);
    }


}
