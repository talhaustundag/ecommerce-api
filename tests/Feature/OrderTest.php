<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_create_order_and_stock_reduces()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::factory()->create([
            'stock' => 5,
            'price' => 200
        ]);

        $cart = Cart::create(['user_id' => $user->id]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response = $this->postJson('/api/orders/create');

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        // stok azalmış mı?
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 3
        ]);
    }
}
