<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Http\Resources\TripResource;
use App\Models\Circle;
use App\Models\Trip;
use App\Support\GroupActivityWriter;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TripController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TripResource::collection(
            TripV1Access::scope(Trip::query(), request()->user(), Trip::class)
                ->with(['circle', 'expenses', 'members.user', 'creator', 'tripPlaces.place', 'tripPlaces.activities'])
                ->latest('created_at')
                ->paginate(25)
        );
    }

    public function store(StoreTripRequest $request): TripResource
    {
        $trip = Trip::query()->create([...$request->validated(), 'creator_id' => $request->user()->getKey()]);
        $circleMembers = $trip->circle_id === null
            ? []
            : (Circle::query()->find($trip->circle_id)?->member_user_ids ?? []);
        GroupActivityWriter::record(array_values(array_unique([
            (string) $request->user()->getKey(),
            ...$circleMembers,
        ])), [
            'category' => 'trip',
            'group_id' => (string) $trip->getKey(),
            'group_name' => $trip->name,
            'title' => 'Un voyage a été créé',
            'description' => $trip->destination_label ?? 'Destination à organiser',
            'actor' => $request->user()->first_name,
            'icon' => 'trip',
            'href' => '/trip/'.$trip->getKey(),
        ]);

        return new TripResource($trip->load(['circle', 'expenses', 'members.user', 'creator', 'tripPlaces.place', 'tripPlaces.activities']));
    }

    public function show(string $id): TripResource
    {
        $model = TripV1Access::scope(Trip::query(), request()->user(), Trip::class)
            ->with(['circle', 'expenses', 'members.user', 'creator', 'tripPlaces.place', 'tripPlaces.activities'])
            ->findOrFail($id);

        return new TripResource($model);
    }

    public function update(UpdateTripRequest $request, string $id): TripResource
    {
        abort_unless(TripV1Access::canManageTrip(request()->user(), $id), 403);
        $model = TripV1Access::scope(Trip::query(), request()->user(), Trip::class)->findOrFail($id);
        $model->update($request->validated());

        return new TripResource($model->refresh()->load(['circle', 'expenses', 'members.user', 'creator', 'tripPlaces.place', 'tripPlaces.activities']));
    }

    public function destroy(string $id): Response
    {
        abort_unless(TripV1Access::canManageTrip(request()->user(), $id), 403);
        $model = TripV1Access::scope(Trip::query(), request()->user(), Trip::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
