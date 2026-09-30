<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $attributes = $this->resource->attributesToArray();

        foreach (array_keys($attributes) as $key) {
            if (preg_match('/password|secret|token|credential|private_key|card_number/i', $key)) {
                unset($attributes[$key]);
            }
        }

        return $attributes;
    }
}
