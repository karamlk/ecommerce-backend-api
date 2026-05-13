<?php

namespace App\Services\Order;

use App\Aspects\ExecutionAspect;
use App\Models\Order;

class DailySalesService
{
    public function __construct(private ExecutionAspect $execution) {}

    public function processChunk(array $orderIds, int $chunkNumber, string $date): array
    {
        return $this->execution->run(
            "DailySalesService::processChunk:{$chunkNumber}",
            function () use ($orderIds, $chunkNumber, $date) {
                $orders  = Order::with('items')->whereIn('id', $orderIds)->get();
                $revenue = 0;
                $items   = 0;

                foreach ($orders as $order) {
                    $revenue += $order->total ?? 0;
                    $items   += $order->items->sum('quantity');
                }

                return [
                    'date'            => $date,
                    'chunk'           => $chunkNumber,
                    'orders_in_chunk' => count($orderIds),
                    'revenue'         => round($revenue, 2),
                    'items_sold'      => $items,
                ];
            }
        );
    }

    public function processWithoutChunks(string $date): array
    {
        return $this->execution->run(
            'DailySalesService::processWithoutChunks',
            function () use ($date) {

                $orders = Order::with('items')
                    ->where('status', 'completed')
                    ->whereDate('created_at', $date)
                    ->get();

                $totalOrders  = 0;
                $totalRevenue = 0;
                $totalItems   = 0;

                foreach ($orders as $order) {
                    $totalOrders++;
                    $totalRevenue += $order->total ?? 0;
                    $totalItems   += $order->items->sum('quantity');
                }

                return [
                    'date'               => $date,
                    'mode'               => 'WITHOUT CHUNKS',
                    'total_orders'       => $totalOrders,
                    'total_revenue'      => round($totalRevenue, 2),
                    'total_items_sold'   => $totalItems,
                ];
            }
        );
    }
}
