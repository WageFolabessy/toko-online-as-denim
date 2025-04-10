<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductsPaginatedResource extends ResourceCollection
{
    public $collects = CollectionResource::class;

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
