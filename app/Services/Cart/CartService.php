<?php

namespace App\Services\Cart;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function getUserCart($userId)
    {
        return CartItem::with('product')
            ->where('user_id', $userId)
            ->get();
    }

    public function addToCart($userId, $productId, $quantity)
    {
        return DB::transaction(function () use ($userId, $productId, $quantity) {

            $product = Product::findOrFail($productId);

            if ($product->stock < $quantity) {
                throw new \Exception('Not enough stock');
            }

            $cartItem = CartItem::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $quantity;

                if ($newQuantity > $product->stock) {
                    throw new \Exception('Not enough stock');
                }

                $cartItem->update([
                    'quantity' => $newQuantity,
                ]);
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

    public function updateCartItem($userId, $cartItemId, $newQuantity)
    {
        return DB::transaction(function () use ($userId, $cartItemId, $newQuantity) {

            $cartItem = CartItem::with('product')
                ->where('id', $cartItemId)
                ->where('user_id', $userId)
                ->first();

            if (!$cartItem) {
                return null;
            }

            if ($newQuantity <= 0) {
                throw new \Exception('Invalid quantity');
            }

            $product = $cartItem->product;

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

    public function removeCartItem($userId, $cartItemId)
    {
        return DB::transaction(function () use ($userId, $cartItemId) {

            $cartItem = CartItem::where('id', $cartItemId)
                ->where('user_id', $userId)
                ->first();

            if (!$cartItem) {
                return null;
            }

            $cartItem->delete();

            return true;
        });
    }

    public function clearCart($userId)
    {
        return DB::transaction(function () use ($userId) {

            CartItem::where('user_id', $userId)->delete();

            return true;
        });
    }
}