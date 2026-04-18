<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,

            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => $this->product->price,
                'photo_url' => $this->product->photo_url,
            ],

            'store' => [
                'id' => $this->product->store->id,
                'name' => $this->product->store->name,
            ],
        ];
    }
}