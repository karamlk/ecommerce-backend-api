<?php

namespace Tests\Unit\Order;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Order\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = new OrderService();
    }

    public function test_order_is_created_successfully_from_cart()
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

        $order = $this->orderService->createOrderFromCart($user->id);

        $this->assertNotNull($order);
        $this->assertEquals($user->id, $order->user_id);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 8, // 10 - 2
        ]);
    }

    public function test_order_returns_null_when_cart_is_empty()
    {
        $user = User::factory()->create();

        $result = $this->orderService->createOrderFromCart($user->id);

        $this->assertNull($result);
    }

    public function test_order_fails_when_stock_is_insufficient()
    {
        $this->expectException(\Exception::class);

        $user = User::factory()->create();

        $product = Product::factory()->create([
            'stock' => 1,
            'price' => 100,
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 5, // more than stock
        ]);

        $this->orderService->createOrderFromCart($user->id);
    }

    public function test_order_deletion_restores_stock()
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'stock' => 10,
            'price' => 100,
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

        $this->orderService->deleteOrder($order->fresh('items.product'));

        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 13, // restored 3
        ]);
    }
}
