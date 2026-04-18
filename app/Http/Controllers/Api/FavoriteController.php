<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteCollection;
use App\Services\Favorite\FavoriteService;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    protected $service;

    public function __construct(FavoriteService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $favorites = $this->service->getUserFavorites(auth('sanctum')->id());

        return new FavoriteCollection($favorites);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        try {
            $this->service->addToFavorites(
                auth('sanctum')->id(),
                $request->product_id
            );

            return response()->json([
                'message' => 'Product added to favorites successfully.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        try {
            $this->service->removeFromFavorites(
                auth('sanctum')->id(),
                $request->product_id
            );

            return response()->json([
                'message' => 'Product removed from favorites successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
