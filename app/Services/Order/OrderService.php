<?php

namespace App\Services\Order;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function getUserOrders($userId)
    {
        return Order::where('user_id', $userId)->get();
    }

    public function getOrderWithItems($orderId, $userId)
    {
        return Order::with(['user', 'items.product'])
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->first();
    }

    public function createOrderFromCart($userId)
    {
        // TASK 2: Resource Management & Capacity Control - Cache::Lock
        return Cache::lock("checkout-global-limit", 5)
            ->block(5, function () use ($userId) {
                // TASK 1: Concurrent Access & Data Integrity
                $cartItems = CartItem::where('user_id', $userId)->get();

                if ($cartItems->isEmpty()) {
                    return null;
                }

                return DB::transaction(function () use ($cartItems, $userId) {

                    $order = Order::create([
                        'user_id' => $userId,
                        'status' => 'pending',
                        'total' => $this->calculateCartTotal($cartItems),
                    ]);

                    $this->processCartItems($order, $cartItems);
                    $this->clearCart($cartItems);

                    return $order;
                });
            });
    }



    public function deleteOrder($order)
    {
        return DB::transaction(function () use ($order) {

            // TASK 1: Concurrent Access & Data Integrity - Lock order items rows
            $order->load(['items' => function ($query) {
                $query->lockForUpdate();
            }]);

            foreach ($order->items as $orderItem) {

                // TASK 1: Concurrent Access & Data Integrity
                $product = Product::where('id', $orderItem->product_id)
                    ->lockForUpdate()
                    ->first();

                $product->increment('stock', $orderItem->quantity);
            }

            $order->items()->delete();
            $order->delete();

            return true;
        });
    }

    private function calculateCartTotal($cartItems)
    {
        return $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });
    }

    private function processCartItems($order, $cartItems)
    {
        foreach ($cartItems as $cartItem) {

            // TASK 1: Concurrent Access & Data Integrity - lock product row
            $product = Product::where('id', $cartItem->product_id)
                ->lockForUpdate()
                ->first();

            if ($product->stock < $cartItem->quantity) {
                throw new \Exception('Not enough stock for product: ' . $product->id);
            }

            $product->decrement('stock', $cartItem->quantity);

            $order->items()->create([
                'product_id' => $product->id,
                'quantity' => $cartItem->quantity,
                'price' => $product->price,
            ]);
        }
    }

    private function clearCart($cartItems)
    {
        $cartItems->each->delete();
    }
}
