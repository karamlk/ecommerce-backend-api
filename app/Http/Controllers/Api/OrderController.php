<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\OrderResource;
use App\Jobs\ProcessOrderJob;
use App\Models\Order;
use App\Services\Order\OrderService;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index()
    {
        $orders = $this->orderService->getUserOrders(auth('sanctum')->id());

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No orders found'], 404);
        }

        return OrderResource::collection($orders);
    }


    public function show($orderId)
    {
        $order = $this->orderService->getOrderWithItems(
            $orderId,
            auth('sanctum')->id()
        );

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return OrderItemResource::collection($order->items);
    }

    // Task 10
    public function store()
    {
        try {

            $userId = auth('sanctum')->id();

            ProcessOrderJob::dispatch(
                $userId
            );

            return response()->json([
                'message' => 'Order accepted and queued for processing'
            ], 202);
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Order creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy($orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', auth('sanctum')->id())
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order not found or not owned by the user'
            ], 404);
        }

        $this->orderService->deleteOrder($order);

        return response()->json([
            'message' => 'Order deleted and stock restored'
        ]);
    }
}
