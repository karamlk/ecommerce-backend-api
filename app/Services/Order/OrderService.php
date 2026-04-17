<?php

namespace App\Services\Order;

use App\Models\CartItem;
use App\Models\Order;
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
    }

    public function deleteOrder($order)
    {
        return DB::transaction(function () use ($order) {

            foreach ($order->items as $orderItem) {
                $orderItem->product->increment('stock', $orderItem->quantity);
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

            $product = $cartItem->product;

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
