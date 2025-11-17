<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product Model",
 *     description="Ürün bilgisi",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Iphone 15 Pro"),
 *     @OA\Property(property="description", type="string", example="256 GB Amiral gemisi telefon"),
 *     @OA\Property(property="price", type="number", example=49999),
 *     @OA\Property(property="stock", type="integer", example=15),
 *     @OA\Property(property="brand", type="string", example="Apple"),
 *     @OA\Property(property="category_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'stock',
        'brand'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
