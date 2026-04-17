<?php

namespace Tests\Feature\Order;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_order_successfully()
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'stock' => 10,
            'price' => 100,
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders');

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'order has been added successfully'
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 8,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id,
        ]);
    }

    public function test_user_cannot_create_order_with_empty_cart()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders');

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'The cart is empty'
            ]);
    }

    public function test_order_fails_when_stock_is_insufficient()
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'stock' => 1,
            'price' => 100,
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders');

        $response->assertStatus(500); // because service throws exception

        $this->assertDatabaseMissing('orders', [
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_get_orders()
    {
        $user = User::factory()->create();

        Order::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'total' => 100,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200);
    }

    public function test_user_can_view_single_order()
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'total' => 100,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200);
    }

    public function test_user_cannot_access_other_users_order()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $order = Order::create([
            'user_id' => $user2->id,
            'status' => 'pending',
            'total' => 100,
        ]);

        Sanctum::actingAs($user1);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(404);
    }

    public function test_user_can_delete_order_and_restore_stock()
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'stock' => 10,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'total' => 100,
        ]);

        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 100,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order deleted and stock restored'
            ]);

        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 13,
        ]);
    }
}
