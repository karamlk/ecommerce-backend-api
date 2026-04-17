<?php

namespace Tests\Feature\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderItemApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_user_cannot_access_order_items()
    {
        $response = $this->getJson('/api/items/1');
        
        $response->assertStatus(401);
    }

    public function test_can_show_order_item()
    {
        Sanctum::actingAs($this->user); 

        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $this->user->id]);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->getJson("/api/items/{$orderItem->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $orderItem->id)
                 ->assertJsonPath('data.quantity', 2);
    }

    public function test_returns_404_when_showing_non_existent_item()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/items/999');

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Item not found']);
    }

    public function test_can_update_order_item_quantity()
    {
        Sanctum::actingAs($this->user);

        $product = Product::factory()->create(['stock' => 10, 'price' => 100]);
        $order = Order::factory()->create(['user_id' => $this->user->id]);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->putJson("/api/items/{$orderItem->id}", [
            'quantity' => 5
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.quantity', 5);
                 
        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'quantity' => 5,
        ]);
    }

    public function test_update_validates_quantity_input()
    {
        Sanctum::actingAs($this->user);

        $orderItem = OrderItem::factory()->create();

        $response1 = $this->putJson("/api/items/{$orderItem->id}", []);
        $response1->assertStatus(422)
                  ->assertJsonValidationErrors('quantity');

        $response2 = $this->putJson("/api/items/{$orderItem->id}", ['quantity' => 0]);
        $response2->assertStatus(422)
                  ->assertJsonValidationErrors('quantity');

        $response3 = $this->putJson("/api/items/{$orderItem->id}", ['quantity' => 'abc']);
        $response3->assertStatus(422)
                  ->assertJsonValidationErrors('quantity');
    }

    public function test_returns_400_when_updating_beyond_available_stock()
    {
        Sanctum::actingAs($this->user);

        $product = Product::factory()->create(['stock' => 2]);
        $order = Order::factory()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->putJson("/api/items/{$orderItem->id}", [
            'quantity' => 5
        ]);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'Not enough stock']);
    }

    public function test_returns_404_when_updating_non_existent_item()
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/items/999', [
            'quantity' => 5
        ]);

        $response->assertStatus(404);
    }

    public function test_can_delete_order_item()
    {
        Sanctum::actingAs($this->user);

        $product = Product::factory()->create();
        $order = Order::factory()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);

        $response = $this->deleteJson("/api/items/{$orderItem->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Item deleted successfully']);

        $this->assertDatabaseMissing('order_items', [
            'id' => $orderItem->id,
        ]);
    }

    public function test_returns_404_when_deleting_non_existent_item()
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/items/999');

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Item not found']);
    }
}