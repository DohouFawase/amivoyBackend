<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLocationPointRequest;
use App\Http\Requests\UpdateLocationPointRequest;
use App\Http\Resources\LocationPointResource;
use App\Models\LocationPoint;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class LocationPointController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return LocationPointResource::collection(TripV1Access::scope(LocationPoint::query(), request()->user(), LocationPoint::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreLocationPointRequest $request): LocationPointResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new LocationPoint)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new LocationPointResource(LocationPoint::create($attributes));
    }

    public function show(string $id): LocationPointResource
    {
        $model = TripV1Access::scope(LocationPoint::query(), request()->user(), LocationPoint::class)->findOrFail($id);

        return new LocationPointResource($model);
    }

    public function update(UpdateLocationPointRequest $request, string $id): LocationPointResource
    {
        $model = TripV1Access::scope(LocationPoint::query(), request()->user(), LocationPoint::class)->findOrFail($id);
        $model->update($request->validated());

        return new LocationPointResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(LocationPoint::query(), request()->user(), LocationPoint::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
