<?php

namespace App\Services\Cart;

use App\Aspects\ExecutionAspect;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function __construct(private ExecutionAspect $execution) {}

    public function getUserCart($userId)
    {
        return $this->execution->run(
            'CartService::getUserCart',
            function () use ($userId) {
                return CartItem::with('product')
                    ->where('user_id', $userId)
                    ->get();
            }
        );
    }

    public function addToCart($userId, $productId, $quantity)
    {
        return $this->execution->run(
            'CartService::addToCart',
            function () use ($userId, $productId, $quantity) {
                return DB::transaction(function () use ($userId, $productId, $quantity) {

                    // Lock user cart rows
                    CartItem::where('user_id', $userId)->lockForUpdate()->get();

                    $cartItem = CartItem::where('user_id', $userId)
                        ->where('product_id', $productId)
                        ->lockForUpdate()
                        ->first();

                    if (!$cartItem) {
                        // TASK 2: Resource Management & Capacity Control
                        $cartCount = CartItem::where('user_id', $userId)->count();

                        if ($cartCount >= 50) {
                            throw new \Exception('Cart capacity reached (Max 50 unique items).');
                        }
                    }

                    // TASK 1: Concurrent Access & Data Integrity
                    $product = Product::where('id', $productId)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($product->stock < $quantity) {
                        throw new \Exception('Not enough stock');
                    }

                    if ($cartItem) {
                        $newQuantity = $cartItem->quantity + $quantity;

                        if ($newQuantity > $product->stock) {
                            throw new \Exception('Not enough stock');
                        }

                        $cartItem->update(['quantity' => $newQuantity]);
                    } else {
                        $cartItem = CartItem::create([
                            'user_id' => $userId,
                            'product_id' => $productId,
                            'quantity' => $quantity,
                        ]);
                    }

                    return $cartItem->load('product');
                });
            }
        );
    }

    public function updateCartItem($userId, $cartItemId, $newQuantity)
    {
        return $this->execution->run(
            'CartService::updateCartItem',
            function () use ($userId, $cartItemId, $newQuantity) {
                return DB::transaction(function () use ($userId, $cartItemId, $newQuantity) {

                    // TASK 1: Concurrent Access & Data Integrity
                    $cartItem = CartItem::with('product')
                        ->where('id', $cartItemId)
                        ->where('user_id', $userId)
                        ->lockForUpdate()
                        ->first();

                    if (!$cartItem) {
                        return null;
                    }

                    if ($newQuantity <= 0) {
                        throw new \Exception('Invalid quantity');
                    }

                    // TASK 1: Concurrent Access & Data Integrity
                    $product = Product::where('id', $cartItem->product_id)->lockForUpdate()->first();

                    $diff = $newQuantity - $cartItem->quantity;

                    if ($diff > $product->stock) {
                        throw new \Exception('Not enough stock');
                    }

                    $cartItem->update([
                        'quantity' => $newQuantity,
                    ]);

                    return $cartItem->fresh('product');
                });
            }
        );
    }

    public function removeCartItem($userId, $cartItemId)
    {
        return $this->execution->run(
            'CartService::removeCartItem',
            function () use ($userId, $cartItemId) {
                return DB::transaction(function () use ($userId, $cartItemId) {

                    // TASK 1: Concurrent Access & Data Integrity
                    $cartItem = CartItem::where('id', $cartItemId)
                        ->where('user_id', $userId)
                        ->lockForUpdate()
                        ->first();

                    if (!$cartItem) {
                        return null;
                    }

                    $cartItem->delete();

                    return true;
                });
            }
        );
    }

    public function clearCart($userId)
    {
        return $this->execution->run(
            'CartService::clearCart',
            function () use ($userId) {
                return DB::transaction(function () use ($userId) {

                    // TASK 1: Concurrent Access & Data Integrity
                    $cartItems = CartItem::where('user_id', $userId)->lockForUpdate()->get();

                    if ($cartItems->isNotEmpty()) {
                        CartItem::where('user_id', $userId)->delete();
                    }

                    return true;
                });
            }
        );
    }
}
