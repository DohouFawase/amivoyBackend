<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class PackingItemResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['label'] = $data['title'] ?? '';
        $data['done'] = (bool) ($data['is_packed'] ?? false);

        return $data;
    }
}
