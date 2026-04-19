<?php

namespace Tests\Feature\ConcurrentData;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Services\Order\OrderService;
use Tests\TestCase;

class ConcurrentDataTest extends TestCase
{
    public function test_race_condition_is_prevented_by_locking()
    {
        $service = new OrderService();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $product = Product::factory()->create([
            'stock' => 10,
            'price' => 100,
        ]);

        CartItem::create([
            'user_id' => $user1->id,
            'product_id' => $product->id,
            'quantity' => 6,
        ]);

        CartItem::create([
            'user_id' => $user2->id,
            'product_id' => $product->id,
            'quantity' => 6,
        ]);

        // simulate overlap
        $service->createOrderFromCart($user1->id);
        
        try {
            $order2 = $service->createOrderFromCart($user2->id);
            $this->fail('Second order should not succeed');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Not enough stock', $e->getMessage());
        }

        $product->refresh();

        // final correct stock must be consistent
        $this->assertEquals(4, $product->stock);
    }
}
