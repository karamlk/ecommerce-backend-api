<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\StoreCartRequest;
use App\Http\Resources\CartItemResource;
use App\Services\Cart\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService  $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $cartItems = $this->cartService->getUserCart(auth('sanctum')->id());

        return CartItemResource::collection($cartItems);
    }

    public function store(StoreCartRequest $request)
    {
        try {
            $cartItem = $this->cartService->addToCart(
                auth('sanctum')->id(),
                $request->product_id,
                $request->quantity
            );

            return new CartItemResource($cartItem);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, $cartItemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $cartItem = $this->cartService->updateCartItem(
                auth('sanctum')->id(),
                $cartItemId,
                $request->quantity
            );

            if (!$cartItem) {
                return response()->json([
                    'message' => 'Cart item not found'
                ], 404);
            }

            return new CartItemResource($cartItem);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy($cartItemId)
    {
        $result = $this->cartService->removeCartItem(
            auth('sanctum')->id(),
            $cartItemId
        );

        if (!$result) {
            return response()->json([
                'message' => 'Cart item not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Item removed from cart'
        ]);
    }

    public function clear()
    {
        $this->cartService->clearCart(auth('sanctum')->id());

        return response()->json([
            'message' => 'Cart cleared successfully'
        ]);
    }
}
