<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmationMail;



class OrderController extends Controller
{
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
    //SİPARİŞ GÜNCELLEME
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:beklemede,hazırlanıyor,kargolandı,teslim_edildi,iptal'
        ]);

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



}
