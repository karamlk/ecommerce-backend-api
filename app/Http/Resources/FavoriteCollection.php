<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FavoriteCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $grouped = $this->collection->groupBy(function ($favorite) {
            return $favorite->product->store->category->id;
        });

        return [
            'categories' => $grouped->map(function ($favorites) {
                $category = $favorites->first()->product->store->category;

                return [
                    'category_name' => $category->name,
                    'favorites' => FavoriteResource::collection($favorites),
                ];
            })->values(),
        ];
    }
}