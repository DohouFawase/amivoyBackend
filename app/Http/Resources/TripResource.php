<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;

class TripResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['title'] = $data['name'] ?? null;
        $data['destination'] = $data['destination_label'] ?? null;
        $data['image'] = $data['cover_url'] ?? null;
        $data['color'] = $data['cover_color'] ?? null;
        $data['next'] = $data['next_label'] ?? null;
        $data['people'] = $data['estimated_members'] ?? 1;
        $data['budget'] = $data['planned_budget'] ?? 0;
        $data['circleId'] = $data['circle_id'] ?? null;
        $data['isPrivate'] = ($data['visibility'] ?? 'private') === 'private';
        $data['circleName'] = $this->resource->relationLoaded('circle')
            ? $this->resource->circle?->name
            : null;
        $data['members'] = [];

        if ($this->resource->relationLoaded('members')) {
            $data['members'] = $this->resource->members
                ->where('status', 'active')
                ->pluck('user')
                ->filter()
                ->map(fn ($user) => $user->first_name ?: $user->email)
                ->values()
                ->all();
        }

        if ($this->resource->relationLoaded('creator') && $this->resource->creator !== null) {
            $data['members'] = array_values(array_unique([
                $this->resource->creator->first_name ?: $this->resource->creator->email,
                ...$data['members'],
            ]));
        }

        $data['members'] = array_values(array_unique([...(array) ($this->resource->member_names ?? []), ...$data['members']]));
        $data['people'] = max($data['people'], count($data['members']));
        $data['spent'] = $this->resource->relationLoaded('expenses')
            ? (int) $this->resource->expenses->sum('amount')
            : null;

        if ($this->resource->relationLoaded('tripPlaces')) {
            $data['stops'] = TripPlaceResource::collection($this->resource->tripPlaces);
        }

        $data['dates'] = $data['display_dates'] ?? ($data['dates'] ?? null);
        $data['days'] = $data['duration_days'] ?? ($data['days'] ?? 1);

        if (! empty($data['start_date']) && ! empty($data['end_date'])) {
            $data['dates'] = $data['start_date'].' — '.$data['end_date'];
            $data['days'] = $this->resource->duration_days ?? max(1, (int) Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date'])) + 1);
            $data['dates'] = $this->resource->display_dates ?? ($data['start_date'].' — '.$data['end_date']);
        }

        return $data;
    }
}
