<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TripPlaceResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['tripId'] = $data['trip_id'] ?? null;
        $data['city'] = $data['city'] ?? $this->resource->place?->name;
        $data['country'] = $data['country'] ?? $this->resource->place?->country;
        $data['arrival'] = $data['stay_start'] ?? null;
        $data['lodging'] = $data['lodging_name'] ?? null;
        $data['lodgingPrice'] = $data['lodging_price'] ?? null;
        $data['lodgingCurrency'] = $data['lodging_currency'] ?? 'XOF';

        if (! empty($data['stay_start']) && ! empty($data['stay_end'])) {
            $data['nights'] = max(1, (int) Carbon::parse($data['stay_start'])->diffInDays(Carbon::parse($data['stay_end'])));
        }

        if ($this->resource->relationLoaded('activities')) {
            $data['activityItems'] = ActivityResource::collection($this->resource->activities);
            $data['activities'] = $this->resource->activities->pluck('title')->values()->all();
        }

        return $data;
    }
}
