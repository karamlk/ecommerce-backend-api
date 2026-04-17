<?php

namespace App\Services\Order;

use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderItemService
{
    public function getItem($itemId)
    {
        return OrderItem::with('product')->find($itemId);
    }

    public function updateItem($itemId, $newQuantity)
    {
        return DB::transaction(function () use ($itemId, $newQuantity) {

            $orderItem = OrderItem::with(['product', 'order.items'])->find($itemId);

            if (!$orderItem) {
                return null;
            }

            $product = $orderItem->product;
            $order = $orderItem->order;

            if (!$order) {
                throw new \Exception('Order not found');
            }

            if ($newQuantity <= 0) {
                throw new \Exception('Invalid quantity');
            }

            if ($newQuantity > ($product->stock + $orderItem->quantity)) {
                throw new \Exception('Not enough stock');
            }

            $quantityDiff = $newQuantity - $orderItem->quantity;

            // Update order item
            $orderItem->update([
                'quantity' => $newQuantity,
                'price' => $product->price,
            ]);

            $this->updateProductStock($product, $quantityDiff);

            $order->load('items');

            $order->update([
                'total' => $order->items->sum(fn($item) => $item->price * $item->quantity)
            ]);

            return $orderItem->fresh('product');
        });
    }

    public function deleteItem($itemId)
    {
        return DB::transaction(function () use ($itemId) {

            $orderItem = OrderItem::with(['product', 'order.items'])->find($itemId);

            if (!$orderItem) {
                return null;
            }

            $product = $orderItem->product;
            $order = $orderItem->order;

            if (!$order) {
                throw new \Exception('Order not found');
            }

            // Restore stock
            $product->increment('stock', $orderItem->quantity);

            // Delete item
            $orderItem->delete();

            $order->load('items');

            $order->update([
                'total' => $order->items->sum(fn($item) => $item->price * $item->quantity)
            ]);

            return true;
        });
    }

    private function updateProductStock($product, $quantityDiff)
    {
        if ($quantityDiff < 0) {
            $product->increment('stock', abs($quantityDiff));
        } else {
            $product->decrement('stock', $quantityDiff);
        }
    }
}
