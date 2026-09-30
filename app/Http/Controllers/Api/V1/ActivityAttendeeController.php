<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreActivityAttendeeRequest;
use App\Http\Requests\UpdateActivityAttendeeRequest;
use App\Http\Resources\ActivityAttendeeResource;
use App\Models\ActivityAttendee;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ActivityAttendeeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ActivityAttendeeResource::collection(TripV1Access::scope(ActivityAttendee::query(), request()->user(), ActivityAttendee::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreActivityAttendeeRequest $request): ActivityAttendeeResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new ActivityAttendee)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new ActivityAttendeeResource(ActivityAttendee::create($attributes));
    }

    public function show(string $id): ActivityAttendeeResource
    {
        $model = TripV1Access::scope(ActivityAttendee::query(), request()->user(), ActivityAttendee::class)->findOrFail($id);

        return new ActivityAttendeeResource($model);
    }

    public function update(UpdateActivityAttendeeRequest $request, string $id): ActivityAttendeeResource
    {
        $model = TripV1Access::scope(ActivityAttendee::query(), request()->user(), ActivityAttendee::class)->findOrFail($id);
        $model->update($request->validated());

        return new ActivityAttendeeResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(ActivityAttendee::query(), request()->user(), ActivityAttendee::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
