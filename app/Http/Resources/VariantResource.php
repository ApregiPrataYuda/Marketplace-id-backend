<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
     public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_product' => $this->name_product,
            'name' => $this->name,
            'created_at' => $this->created_at?->toDateString() ?? '-',
            'updated_at' => $this->updated_at?->toDateString() ?? '-',
        ];
    }
}
