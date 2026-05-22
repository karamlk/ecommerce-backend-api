<?php

namespace App\Services\Product;

use App\Aspects\CacheAspect;
use App\Aspects\ExecutionAspect;
use App\Models\Product;
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


    public function getTrendingProducts(int $limit = 10): mixed
    {

        return $this->execution->run(
            'ProductService::getTrendingProducts',
            fn() => $this->cache->remember(
                "trending_products",
                $this->trendingTtl,
                function () use ($limit) {

                    $ids = Redis::zrevrange(
                        'product_popularity',
                        0,
                        $limit - 1
                    );

                    if (empty($ids)) {

                        return Product::latest()
                            ->take($limit)
                            ->get();
                    }

                    return Product::whereIn(
                        'id',
                        $ids
                    )->get();
                }
            )
        );
    }

    public function invalidateProductCache(int $storeId, int $productId): void
    {
        $this->cache->forget("product:{$storeId}:{$productId}");

        $this->cache->forget("trending_products");
    }
}
