<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class TripMemberResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $user = $this->resource->relationLoaded('user') ? $this->resource->user : null;
        $data['user'] = $user === null ? null : [
            'first_name' => $user->first_name,
        ];

        return $data;
    }
}
