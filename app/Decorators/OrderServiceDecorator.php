<?php

namespace App\Decorators;

class OrderServiceDecorator extends BaseServiceDecorator
{
    public function getUserOrders($userId): mixed
    {
        return $this->run('getUserOrders', [$userId]);
    }

    public function getOrderWithItems($orderId, $userId): mixed
    {
        return $this->run('getOrderWithItems', [$orderId, $userId]);
    }

    public function createOrderFromCart($userId, $debug = false)
    {
        return $this->run('createOrderFromCart', [$userId, $debug]);
    }

    public function deleteOrder($order): mixed
    {
        return $this->run('deleteOrder', [$order], true);
    }

    public function createOrderFromCartWithoutLock($userId, $debug = false): mixed
    {
        return $this->run('createOrderFromCartWithoutLock', [$userId, $debug], true);
    }
}
