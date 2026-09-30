<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ExpenseResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['name'] = $data['title'] ?? '';
        $data['emoji'] = $data['icon'] ?? null;
        $data['who'] = $this->resource->relationLoaded('paid_by')
            ? ($this->resource->paid_by?->user?->first_name ?? $this->resource->paid_by?->user?->email)
            : null;

        return $data;
    }
}
