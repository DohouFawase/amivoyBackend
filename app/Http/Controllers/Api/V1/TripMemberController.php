<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTripMemberRequest;
use App\Http\Requests\UpdateTripMemberRequest;
use App\Http\Resources\TripMemberResource;
use App\Models\TripMember;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TripMemberController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TripMemberResource::collection(TripV1Access::scope(TripMember::query(), request()->user(), TripMember::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreTripMemberRequest $request): TripMemberResource
    {
        $attributes = $request->validated();

        foreach (['creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new TripMember)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new TripMemberResource(TripMember::create($attributes));
    }

    public function show(string $id): TripMemberResource
    {
        $model = TripV1Access::scope(TripMember::query(), request()->user(), TripMember::class)->findOrFail($id);

        return new TripMemberResource($model);
    }

    public function update(UpdateTripMemberRequest $request, string $id): TripMemberResource
    {
        $model = TripV1Access::scope(TripMember::query(), request()->user(), TripMember::class)->findOrFail($id);
        $model->update($request->validated());

        return new TripMemberResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(TripMember::query(), request()->user(), TripMember::class)->findOrFail($id);

        abort_unless(
            (string) $model->user_id === (string) request()->user()->id
                || TripV1Access::canManageTrip(request()->user(), (string) $model->trip_id),
            403,
        );

        $model->delete();

        return response()->noContent();
    }
}
