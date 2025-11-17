<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_create_category()
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->postJson('/api/admin/categories', [
            'name' => 'Elektronik'
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Elektronik'
        ]);
    }
}
