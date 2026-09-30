<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class OutingResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['circleId'] = $data['circle_id'] ?? null;
        $data['circleName'] = $this->resource->relationLoaded('circle') ? $this->resource->circle?->name : null;
        $data['locationType'] = $data['location_type'] ?? 'public';
        $data['date'] = $data['date_label'] ?? null;
        $data['time'] = $data['time_label'] ?? null;
        $data['budgetTarget'] = $data['budget_target'] ?? null;
        $data['checkedIn'] = $data['checked_in'] ?? [];
        $data['participantUserIds'] = $data['participant_user_ids'] ?? [];
        $data['startedAt'] = $data['started_at'] ?? null;
        $data['endedAt'] = $data['ended_at'] ?? null;
        $data['contributions'] = array_map(static fn (array $contribution): array => [
            ...$contribution,
            'createdAt' => $contribution['createdAt'] ?? $contribution['created_at'] ?? null,
        ], $data['contributions'] ?? []);

        if ($this->resource->relationLoaded('photos')) {
            $data['photos'] = PhotoResource::collection($this->resource->photos);
        }

        return $data;
    }
}
