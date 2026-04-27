<?php

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function created(Order $order): void
    {
        Log::channel('activity')->info('[ORDER CREATED]', [
            'order_id' => $order->id,
            'user_id'  => $order->user_id,
            'total'    => $order->total,
        ]);
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            Log::channel('activity')->info('[ORDER STATUS CHANGED]', [
                'order_id'   => $order->id,
                'old_status' => $order->getOriginal('status'),
                'new_status' => $order->status,
            ]);
        }
    }

    public function deleted(Order $order): void
    {
        Log::channel('activity')->warning('[ORDER DELETED]', [
            'order_id' => $order->id,
        ]);
    }
}
