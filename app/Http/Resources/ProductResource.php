<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_product' => $this->name_product,
            'name_category' => $this->name_category,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'variant' => $this->variant,
            'stock' => $this->stock,
            'status' => $this->status,
            'image_url' => $this->image_url,
            'created_at' => $this->created_at?->toDateString() ?? '-',
            'updated_at' => $this->updated_at?->toDateString() ?? '-',
        ];
    }
}
