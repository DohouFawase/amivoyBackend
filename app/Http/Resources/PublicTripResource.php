<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicTripResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'cover_url' => $this->cover_url,
            'destination_label' => $this->destination_label,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ];
    }
}
