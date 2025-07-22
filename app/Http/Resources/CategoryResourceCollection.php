<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\CategoryResource;

class CategoryResourceCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => CategoryResource::collection($this->collection),
            'pagination' => [
                'total' => $this->total(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'next_page_url' => $this->nextPageUrl(),
                'prev_page_url' => $this->previousPageUrl(),
            ],
        ];
    }
}
