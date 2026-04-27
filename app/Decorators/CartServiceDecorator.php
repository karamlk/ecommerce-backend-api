<?php

namespace App\Decorators;

class CartServiceDecorator extends BaseServiceDecorator
{
    public function getUserCart($userId)
    {
        return $this->run('getUserCart', [$userId]);
    }

    public function addToCart($userId, $productId, $quantity)
    {
        return $this->run('addToCart', [$userId, $productId, $quantity]);
    }

    public function updateCartItem($userId, $cartItemId, $newQuantity)
    {
        return $this->run('updateCartItem', [$userId, $cartItemId, $newQuantity]);
    }

    public function removeCartItem($userId, $cartItemId)
    {
        return $this->run('removeCartItem', [$userId, $cartItemId]);
    }

    public function clearCart($userId)
    {
        return $this->run('clearCart', [$userId]);
    }
}
