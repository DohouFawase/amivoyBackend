<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMeetingPointRequest;
use App\Http\Requests\UpdateMeetingPointRequest;
use App\Http\Resources\MeetingPointResource;
use App\Models\MeetingPoint;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class MeetingPointController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return MeetingPointResource::collection(TripV1Access::scope(MeetingPoint::query(), request()->user(), MeetingPoint::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreMeetingPointRequest $request): MeetingPointResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new MeetingPoint)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new MeetingPointResource(MeetingPoint::create($attributes));
    }

    public function show(string $id): MeetingPointResource
    {
        $model = TripV1Access::scope(MeetingPoint::query(), request()->user(), MeetingPoint::class)->findOrFail($id);

        return new MeetingPointResource($model);
    }

    public function update(UpdateMeetingPointRequest $request, string $id): MeetingPointResource
    {
        $model = TripV1Access::scope(MeetingPoint::query(), request()->user(), MeetingPoint::class)->findOrFail($id);
        $model->update($request->validated());

        return new MeetingPointResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(MeetingPoint::query(), request()->user(), MeetingPoint::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
