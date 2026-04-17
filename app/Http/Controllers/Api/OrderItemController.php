<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderItemResource;
use App\Services\Order\OrderItemService;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    protected $service;

    public function __construct(OrderItemService $service)
    {
        $this->service = $service;
    }

    public function show($itemId)
    {
        $item = $this->service->getItem($itemId);

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        return new OrderItemResource($item);
    }

    public function update($itemId, Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        try {

            $item = $this->service->updateItem($itemId, $request->input('quantity'));

            if (!$item) {
                return response()->json(['message' => 'Item not found'], 404);
            }

            return new OrderItemResource($item);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy($itemId)
    {
        try {
            $result = $this->service->deleteItem($itemId);

            if (!$result) {
                return response()->json(['message' => 'Item not found'], 404);
            }

            return response()->json(['message' => 'Item deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
