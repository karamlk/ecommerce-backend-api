<?php

namespace App\Services\Product;

use App\Aspects\CacheAspect;
use App\Aspects\ExecutionAspect;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ProductService
{
    private int $productTtl  = 120;
    private int $trendingTtl = 600;

    public function __construct(
        private ExecutionAspect $execution,
        private CacheAspect     $cache,
    ) {}

    public function getStoreProducts(int $storeId): mixed
    {
        return $this->execution->run(
            'ProductService::getStoreProducts',
            fn() => Product::where(
                'store_id',
                $storeId
            )->get()
        );
    }

    // Task 6: Distributed caching
    public function getProduct(int $storeId, int $productId): mixed
    {
        Redis::zincrby(
            'product_popularity',
            1,
            $productId
        );

        return $this->execution->run(
            'ProductService::getProduct',
            fn() => $this->cache->remember(
                "product:{$storeId}:{$productId}",
                $this->productTtl,
                fn() =>
                Product::where('store_id', $storeId)
                    ->findOrFail($productId)
            )
        );
    }

    // Home products — shows trending if available, falls back to latest
    public function getHomeProducts(): mixed
    {
        return $this->getTrendingProducts(10);
    }

    // Task 6: Distributed caching
    public function getTrendingProducts(int $limit = 10): mixed
{
    return $this->execution->run(
        'ProductService::getTrendingProducts',
        fn() => $this->cache->remember(
            "trending_products",
            $this->trendingTtl,
            function () use ($limit) {
                $ids = Redis::zrevrange('product_popularity', 0, $limit - 1);

                if (empty($ids)) {
                    return Product::select(['id', 'name', 'price', 'store_id', 'photo_url'])
                        ->latest('id') 
                        ->take($limit)
                        ->get();
                }


                $idsString = implode(',', $ids);
                
                return Product::select(['id', 'name', 'price', 'store_id', 'photo_url'])
                    ->whereIn('id', $ids)
                    ->orderByRaw("FIELD(id, {$idsString})")
                    ->get();
            }
        )
    );
}


    public function updateProduct(int $productId, array $data): bool
    {
        return $this->execution->run(
            'ProductService::updateProduct',
            function () use ($productId, $data) {

                $product = Product::findOrFail($productId);
                sleep(3);
                // TASK 7: Optimistic Locking
                $affected = DB::table('products')
                    ->where('id', $productId)
                    ->where('version', $product->version)
                    ->update(array_merge($data, [
                        'version' => $product->version + 1,
                    ]));

                if ($affected === 0) {
                    throw new \Exception(
                        'Product was modified by another request. Please try again.'
                    );
                }

                $this->invalidateProductCache($productId, $product->store_id);

                Log::channel('activity')->info('[OPTIMISTIC LOCK] Product updated', [
                    'product_id' => $productId,
                    'version'    => $product->version + 1,
                    'changed'    => array_keys($data),
                ]);

                return true;
            }
        );
    }

    public function updateProductWithoutOptimisticLock(int $productId, array $data): bool
    {
        return $this->execution->run(
            'ProductService::updateProductWithoutOptimisticLock',
            function () use ($productId, $data) {

                $product = Product::findOrFail($productId);

                sleep(3);

                $product->update($data);

                Log::channel('activity')->info(
                    '[NO LOCK] Product updated',
                    [
                        'product_id' => $productId,
                        'data' => $data,
                    ]
                );

                return true;
            }
        );
    }

    // Task 6: Distributed caching
    public function invalidateProductCache(int $storeId, int $productId): void
    {
        $this->cache->forget("product:{$storeId}:{$productId}");

        $this->cache->forget("trending_products");
    }
}
