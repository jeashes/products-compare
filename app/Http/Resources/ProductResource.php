<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'image' => $this->image,
            'price' => $this->price,
            'rating' => $this->rating,
            'pros' => $this->pros,
            'cons' => $this->cons,
            'key_features' => $this->key_features,
            'trending_order' => $this->trending_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}