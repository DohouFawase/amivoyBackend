<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGroupActivityRequest;
use App\Http\Resources\GroupActivityResource;
use App\Models\Circle;
use App\Models\GroupActivity;
use App\Models\Outing;
use App\Models\Trip;
use App\Models\TripMember;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GroupActivityController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return GroupActivityResource::collection(
            GroupActivity::query()->forUser(request()->user())->latest('created_at')->paginate(25)
        );
    }

    public function store(StoreGroupActivityRequest $request): GroupActivityResource
    {
        $attributes = $request->validated();
        $this->authorizeGroup($attributes['category'], $attributes['group_id']);

        $recipients = match ($attributes['category']) {
            'circle' => Circle::query()->findOrFail($attributes['group_id'])->member_user_ids ?? [],
            'outing' => Outing::query()->findOrFail($attributes['group_id'])->participant_user_ids ?? [],
            'trip' => TripMember::query()
                ->where('trip_id', $attributes['group_id'])
                ->where('status', 'active')
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->map(fn ($id) => (string) $id)
                ->all(),
        };

        if ($attributes['category'] === 'trip') {
            $tripCreator = Trip::query()->whereKey($attributes['group_id'])->value('creator_id');
            if ($tripCreator !== null) {
                $recipients[] = (string) $tripCreator;
            }
        }

        $recipients[] = (string) $request->user()->getKey();
        $timeLabel = now()->locale('fr')->translatedFormat('d F · H:i');
        $activity = null;

        foreach (array_unique($recipients) as $recipientId) {
            $record = GroupActivity::query()->create([
                ...$attributes,
                'user_id' => $recipientId,
                'actor' => $request->user()->first_name ?: $request->user()->email,
                'time_label' => $timeLabel,
            ]);

            if ((string) $recipientId === (string) $request->user()->getKey()) {
                $activity = $record;
            }
        }

        return new GroupActivityResource($activity);
    }

    private function authorizeGroup(string $category, string $groupId): void
    {
        $user = request()->user();

        $isAccessible = match ($category) {
            'circle' => Circle::query()->accessibleTo($user)->whereKey($groupId)->exists(),
            'outing' => Outing::query()->accessibleTo($user)->whereKey($groupId)->exists(),
            'trip' => TripV1Access::scope(Trip::query(), $user, Trip::class)->whereKey($groupId)->exists(),
            default => false,
        };

        abort_unless($isAccessible, 403);
    }
}
