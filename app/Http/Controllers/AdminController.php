<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminController extends Controller
{
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
            'message' => 'Dashboard verileri getirildi.',
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
