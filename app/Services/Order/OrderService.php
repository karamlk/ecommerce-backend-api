<?php

namespace App\Services\Order;

use App\Aspects\ExecutionAspect;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(private ExecutionAspect $execution) {}

    public function getUserOrders($userId)
    {
        return $this->execution->run(
            'OrderService::getUserOrders',
            fn() => Order::where('user_id', $userId)->get()
        );
    }

    public function getOrderWithItems($orderId, $userId)
    {
        return $this->execution->run(
            'OrderService::getOrderWithItems',
            fn() => Order::with(['user', 'items.product'])
                ->where('id', $orderId)
                ->where('user_id', $userId)
                ->first()
        );
    }

    public function createOrderFromCart($userId, $debug = false)
    {
        return $this->execution->run(
            'OrderService::createOrderFromCart',
            function () use ($userId, $debug) {

                // TASK 2: Resource Management & Capacity Control - Cache::Lock
                return Cache::lock("checkout-global-limit", 5)
                    ->block(5, function () use ($userId, $debug) {

                        // TASK 1: Concurrent Access & Data Integrity
                        $cartItems = CartItem::where('user_id', $userId)->get();

                        if ($cartItems->isEmpty()) {
                            return null;
                        }

                        return DB::transaction(function () use ($cartItems, $userId, $debug) {

                            $order = Order::create([
                                'user_id' => $userId,
                                'status'  => 'pending',
                                'total'   => $this->calculateCartTotal($cartItems),
                            ]);

                            $this->processCartItems($order, $cartItems, $debug);
                            $this->clearCart($cartItems);

                            return $order;
                        });
                    });
            }
        );
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

    private function processCartItems($order, $cartItems, $debug = false)
    {
        foreach ($cartItems as $cartItem) {

            // TASK 1: Concurrent Access & Data Integrity - lock product row
            $product = Product::where('id', $cartItem->product_id)
                ->lockForUpdate()
                ->first();

            if ($debug) {
                sleep(3);
            }

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

    public function createOrderFromCartWithoutLock($userId, $debug = false)
    {
        return $this->execution->run(
            'OrderService::createOrderFromCartWithoutLock',
            function () use ($userId, $debug) {

                // TASK 1: Concurrent Access & Data Integrity
                $cartItems = CartItem::where('user_id', $userId)->get();

                if ($cartItems->isEmpty()) {
                    return null;
                }

                return DB::transaction(function () use ($cartItems, $userId, $debug) {

                    $order = Order::create([
                        'user_id' => $userId,
                        'status'  => 'pending',
                        'total'   => $this->calculateCartTotal($cartItems),
                    ]);

                    $this->processCartItemsWithoutLock($order, $cartItems, $debug);
                    $this->clearCart($cartItems);

                    return $order;
                });
            }
        );
    }

    private function processCartItemsWithoutLock($order, $cartItems, $debug = false)
    {
        foreach ($cartItems as $cartItem) {

            $product = Product::where('id', $cartItem->product_id)->first();

            if ($debug) {
                sleep(3);
            }

            $product->decrement('stock', $cartItem->quantity);

            $order->items()->create([
                'product_id' => $product->id,
                'quantity'   => $cartItem->quantity,
                'price'      => $product->price,
            ]);
        }
    }
}
