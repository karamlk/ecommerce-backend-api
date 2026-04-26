<?php

namespace Tests\Unit\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\Order\OrderItemService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderItemServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrderItemService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OrderItemService::class);
    }

    public function test_update_order_item_increases_quantity_and_decreases_stock()
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'stock' => 10,
            'price' => 100,
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 100,
        ]);

        $this->service->updateItem($orderItem->id, 5);

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'quantity' => 5,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 7, // 10 - (5 - 2)
        ]);
    }

    public function test_update_order_item_decreases_quantity_and_restores_stock()
    {
        $product = Product::factory()->create([
            'stock' => 5,
            'price' => 100,
        ]);

        $order = Order::factory()->create();

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $this->service->updateItem($orderItem->id, 2);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 8, // 5 + (5 - 2)
        ]);
    }

    public function test_update_order_item_fails_when_stock_not_enough()
    {
        $this->expectException(\Exception::class);

        $product = Product::factory()->create([
            'stock' => 1,
        ]);

        $order = Order::factory()->create();

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // trying to increase beyond available stock
        $this->service->updateItem($orderItem->id, 10);
    }

    public function test_delete_order_item_removes_item_and_restores_stock()
    {
        $product = Product::factory()->create([
            'stock' => 5,
        ]);

        $order = Order::factory()->create();

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->service->deleteItem($orderItem->id);

        $this->assertDatabaseMissing('order_items', [
            'id' => $orderItem->id,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 8, // 5 + 3
        ]);
    }

    public function test_order_total_is_updated_after_update()
    {
        $product = Product::factory()->create([
            'price' => 100,
            'stock' => 10,
        ]);

        $order = Order::factory()->create([
            'total' => 200,
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 100,
        ]);

        $this->service->updateItem($orderItem->id, 3);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'total' => 300,
        ]);
    }

    public function test_order_total_is_updated_after_delete()
    {
        $product = Product::factory()->create([
            'price' => 100,
        ]);

        $order = Order::factory()->create([
            'total' => 300,
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 100,
        ]);

        $this->service->deleteItem($orderItem->id);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'total' => 0,
        ]);
    }
}
