<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function createOrder()
    {
        $cart = Cart::where('user_id', auth()->id())
            ->with('items.product')
            ->first();

        if (!$cart || $cart->items->count() == 0) {
            return response()->json([
                'message' => 'Sepet boş.'
            ], 400);
        }

        $total = 0;

        // TOPLAM TUTARI HESAPLAMA
        foreach ($cart->items as $item) {
            $total += $item->product->price * $item->quantity;
        }

        // SİPARİŞ OLUŞTURMA
        $order = Order::create([
            'user_id' => auth()->id(),
            'total_amount' => $total,
            'status' => 'pending'
        ]);

        // ORDER ITEMS KAYDET - STOK DÜŞ
        foreach ($cart->items as $item) {

            // STOK KONTROLÜ
            if ($item->product->stock < $item->quantity) {
                return response()->json([
                    'message' => $item->product->name . ' için yeterli stok yok.'
                ], 400);
            }

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

        return response()->json([
            'success' => true,
            'message' => 'Sipariş başarıyla oluşturuldu.',
            'order' => $order->load('items.product')
        ], 201);
    }

    //SİPARİŞ LİSTELEME
    public function listOrders()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with('items.product')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $orders
        ], 200);
    }


}
