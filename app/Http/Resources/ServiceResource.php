<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ServiceResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['name'] = $data['title'] ?? null;
        $data['kind'] = $data['category'] ?? null;

        return $data;
    }
}
