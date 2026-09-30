<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ActivityResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['tripId'] = $data['trip_id'] ?? null;
        $data['type'] = $data['category'] ?? null;
        $data['startsAt'] = $data['starts_at'] ?? null;
        $data['time'] = $data['time_label'] ?? ($data['starts_at'] ? Carbon::parse($data['starts_at'])->format('H:i') : null);
        $data['emoji'] = $data['icon'] ?? null;
        $data['location'] = $data['location_label'] ?? null;

        if ($this->resource->relationLoaded('trip_place') && $this->resource->trip_place !== null) {
            $data['location'] = $this->resource->trip_place->place?->address
                ?? $this->resource->trip_place->place?->name;
        }

        return $data;
    }
}
