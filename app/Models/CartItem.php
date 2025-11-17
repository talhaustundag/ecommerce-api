<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="CartItem",
 *     type="object",
 *     title="CartItem Model",
 *     description="Sepetteki tek ürün bilgisi",
 *     @OA\Property(property="id", type="integer", example=8),
 *     @OA\Property(property="cart_id", type="integer", example=3),
 *     @OA\Property(property="product_id", type="integer", example=2),
 *     @OA\Property(property="quantity", type="integer", example=2),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class CartItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
