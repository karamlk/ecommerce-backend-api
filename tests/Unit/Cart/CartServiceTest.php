<?php

namespace Tests\Unit\Cart;

use App\Models\User;
use App\Models\Product;
use App\Models\CartItem;
use App\Services\Cart\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CartService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CartService::class);
    }

    public function test_it_adds_product_to_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->service->addToCart($user->id, $product->id, 2);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_it_increments_quantity_if_product_already_in_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->service->addToCart($user->id, $product->id, 3);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);
    }

    public function test_it_fails_when_not_enough_stock_on_add()
    {
        $this->expectException(\Exception::class);

        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 2]);

        $this->service->addToCart($user->id, $product->id, 5);
    }

    public function test_it_updates_cart_item_quantity()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $cartItem = CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->service->updateCartItem($user->id, $cartItem->id, 5);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 5,
        ]);
    }

    public function test_it_fails_when_not_enough_stock_on_update()
    {
        $this->expectException(\Exception::class);

        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 2]);

        $cartItem = CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->service->updateCartItem($user->id, $cartItem->id, 5);
    }

    public function test_it_deletes_cart_item()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cartItem = CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->service->removeCartItem($user->id, $cartItem->id);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    public function test_it_returns_user_cart_items()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $cart = $this->service->getUserCart($user->id);

        $this->assertCount(1, $cart);
    }

    public function test_it_clears_user_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->service->clearCart($user->id);

        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id,
        ]);
    }
}