<?php

namespace App\Observers;

use App\Models\CartItem;
use Illuminate\Support\Facades\Log;

class CartItemObserver
{
    public function created(CartItem $cartItem): void
    {
        Log::channel('activity')->info('[CART ITEM ADDED]', [
            'user_id'    => $cartItem->user_id,
            'product_id' => $cartItem->product_id,
            'quantity'   => $cartItem->quantity,
        ]);
    }

    public function deleted(CartItem $cartItem): void
    {
        Log::channel('activity')->info('[CART ITEM REMOVED]', [
            'user_id'    => $cartItem->user_id,
            'product_id' => $cartItem->product_id,
        ]);
    }
}