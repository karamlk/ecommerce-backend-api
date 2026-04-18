<?php

namespace Tests\Feature\Cart;

use App\Models\User;
use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_cart_items()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create();

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->getJson('/api/cart');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    public function test_user_can_add_product_to_cart()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create(['stock' => 10]);

        $response = $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response->assertStatus(201);
                 
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    public function test_user_cannot_add_product_with_insufficient_stock()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create(['stock' => 2]);

        $response = $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'Not enough stock']);
    }

    public function test_adding_same_product_twice_increments_quantity()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create(['stock' => 10]);

        // First add
        $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Second add
        $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);
    }

    public function test_user_can_update_cart_item_quantity()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create(['stock' => 10]);

        $cartItem = CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->putJson("/api/cart/{$cartItem->id}", [
            'quantity' => 5,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 5,
        ]);
    }

    public function test_user_cannot_update_with_insufficient_stock()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create(['stock' => 2]);

        $cartItem = CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->putJson("/api/cart/{$cartItem->id}", [
            'quantity' => 5,
        ]);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'Not enough stock']);
    }

    public function test_user_can_delete_single_cart_item()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create();

        $cartItem = CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->deleteJson("/api/cart/{$cartItem->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Item removed from cart']);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    public function test_user_can_clear_cart()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create();

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->deleteJson('/api/cart');

        $response->assertStatus(200);

        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id,
        ]);
    }

    public function test_guest_cannot_access_cart_routes()
    {
        $response = $this->getJson('/api/cart');

        $response->assertStatus(401);
    }
}