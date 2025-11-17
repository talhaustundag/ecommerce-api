<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="OrderItem",
 *     type="object",
 *     title="OrderItem Model",
 *     description="Siparişteki tek ürün bilgisi",
 *     @OA\Property(property="id", type="integer", example=5),
 *     @OA\Property(property="order_id", type="integer", example=10),
 *     @OA\Property(property="product_id", type="integer", example=2),
 *     @OA\Property(property="quantity", type="integer", example=3),
 *     @OA\Property(property="price", type="number", example=1499),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class OrderItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
