<?php

namespace App\Services\Favorite;

use App\Aspects\ExecutionAspect;
use App\Models\Favorite;
use App\Models\Product;

class FavoriteService
{
    public function __construct(private ExecutionAspect $execution) {}

    public function getUserFavorites($userId)
    {
        return $this->execution->run('FavoriteService::getUserFavorites',
            fn() => Favorite::where('user_id', $userId)
                ->with(['product.store.category'])
                ->get()
        );
    }

    public function addToFavorites($userId, $productId)
    {
        return $this->execution->run('FavoriteService::addToFavorites',
            function () use ($userId, $productId) {

                $existing = Favorite::where('user_id', $userId)
                    ->where('product_id', $productId)
                    ->first();

                if ($existing) {
                    throw new \Exception('This product is already in your favorites.');
                }

                $product = Product::with('store.category')->findOrFail($productId);

                $categoryId = $product->store->category->id;

                return Favorite::create([
                    'user_id'     => $userId,
                    'product_id'  => $productId,
                    'category_id' => $categoryId,
                ]);
            }
        );
    }

    public function removeFromFavorites($userId, $productId)
    {
        return $this->execution->run('FavoriteService::removeFromFavorites',
            function () use ($userId, $productId) {

                $favorite = Favorite::where('user_id', $userId)
                    ->where('product_id', $productId)
                    ->first();

                if (!$favorite) {
                    throw new \Exception('This product is not in your favorites.');
                }

                $favorite->delete();

                return true;
            }
        );
    }
}
