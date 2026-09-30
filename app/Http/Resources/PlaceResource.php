<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class PlaceResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['latitude'] = isset($data['lat']) ? (float) $data['lat'] : null;
        $data['longitude'] = isset($data['lng']) ? (float) $data['lng'] : null;
        $data['emoji'] = $data['cached_data']['emoji'] ?? null;
        $data['isCountry'] = ($data['place_type'] ?? null) === 'country';
        $data['zoom'] = $data['cached_data']['zoom'] ?? null;
        $data['description'] = $data['address'] ?? $data['name'] ?? '';

        return $data;
    }
}
