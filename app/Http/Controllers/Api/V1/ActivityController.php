<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ActivityController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ActivityResource::collection(TripV1Access::scope(Activity::query(), request()->user(), Activity::class)->with('trip_place.place')->latest('created_at')->paginate(25));
    }

    public function store(StoreActivityRequest $request): ActivityResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Activity)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new ActivityResource(Activity::create($attributes)->load('trip_place.place'));
    }

    public function show(string $id): ActivityResource
    {
        $model = TripV1Access::scope(Activity::query(), request()->user(), Activity::class)->findOrFail($id);

        return new ActivityResource($model->load('trip_place.place'));
    }

    public function update(UpdateActivityRequest $request, string $id): ActivityResource
    {
        $model = TripV1Access::scope(Activity::query(), request()->user(), Activity::class)->findOrFail($id);
        $model->update($request->validated());

        return new ActivityResource($model->refresh()->load('trip_place.place'));
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(Activity::query(), request()->user(), Activity::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
