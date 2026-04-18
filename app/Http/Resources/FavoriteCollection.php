<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Category;

class FavoriteCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $grouped = $this->collection->groupBy('category_id');

        $categories = Category::whereIn('id', $grouped->keys())
            ->get()
            ->keyBy('id');

        return [
            'categories' => $grouped->map(function ($favorites, $categoryId) use ($categories) {
                return [
                    'category_name' => $categories[$categoryId]->name ?? null,
                    'favorites' => FavoriteResource::collection($favorites),
                ];
            })->values(),
        ];
    }
}