<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PostCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "collection" => $this->collection,
            "links" => $this->linkCollection(),
            "meta" => [
                "current_page" => $this->currentPage(),
                "last_page" => $this->lastPage(),
                "per_page" => $this->perPage(),
                "from" => $this->firstItem(),
                "to" => $this->lastItem(),
                "total" => $this->total(),
                "path" => $this->path(),
            ]
        ];
    }
}
