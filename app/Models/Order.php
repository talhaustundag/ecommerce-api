<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     title="Order Model",
 *     description="Sipariş bilgisi",
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="total_amount", type="number", example=12999),
 *     @OA\Property(property="status", type="string", example="beklemede"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'total_amount', 'status'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
