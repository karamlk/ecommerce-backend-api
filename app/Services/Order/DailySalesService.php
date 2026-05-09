<?php

namespace App\Services\Order;

use App\Aspects\ExecutionAspect;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

// Task 4
class DailySalesService
{
    public function __construct(private ExecutionAspect $execution) {}

    public function process(string $date): array
    {
        return $this->execution->run(
            'DailySalesService::process',
            function () use ($date) {
                $totalOrders  = 0;
                $totalRevenue = 0;
                $totalItems   = 0;
                $chunkNumber  = 0;
                $baseMemory   = memory_get_usage(); // baseline before chunks
                $peakDelta    = 0;

                Order::with('items')
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [
                        now()->startOfDay(),
                        now()->endOfDay(),
                    ])
                    ->chunkById(100, function ($orders) use (
                        &$totalOrders,
                        &$totalRevenue,
                        &$totalItems,
                        &$chunkNumber,
                        &$peakDelta,
                        $baseMemory,
                        $date,
                    ) {
                        $chunkNumber++;

                        foreach ($orders as $order) {
                            $totalOrders++;
                            $totalRevenue += $order->total;
                            $totalItems   += $order->items->sum('quantity');
                        }

                        // Delta from baseline
                        $delta = memory_get_usage() - $baseMemory;
                        if ($delta > $peakDelta) {
                            $peakDelta = $delta;
                        }

                        Log::channel('activity')->info("[DAILY SALES] Chunk {$chunkNumber} complete", [
                            'orders_in_chunk'  => $orders->count(),
                            'memory_delta_kb'  => round($delta / 1024, 1),
                        ]);
                    });

                return [
                    'date'              => $date,
                    'total_orders'      => $totalOrders,
                    'total_revenue'     => round($totalRevenue, 2),
                    'total_items_sold'  => $totalItems,
                    'total_chunks'      => $chunkNumber,
                    'peak_memory_delta_kb' => round($peakDelta / 1024, 1),
                ];
            }
        );
    }
}
