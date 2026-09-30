<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLocationShareRequest;
use App\Http\Requests\UpdateLocationShareRequest;
use App\Http\Resources\LocationShareResource;
use App\Models\LocationShare;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class LocationShareController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return LocationShareResource::collection(TripV1Access::scope(LocationShare::query(), request()->user(), LocationShare::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreLocationShareRequest $request): LocationShareResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new LocationShare)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new LocationShareResource(LocationShare::create($attributes));
    }

    public function show(string $id): LocationShareResource
    {
        $model = TripV1Access::scope(LocationShare::query(), request()->user(), LocationShare::class)->findOrFail($id);

        return new LocationShareResource($model);
    }

    public function update(UpdateLocationShareRequest $request, string $id): LocationShareResource
    {
        $model = TripV1Access::scope(LocationShare::query(), request()->user(), LocationShare::class)->findOrFail($id);
        $model->update($request->validated());

        return new LocationShareResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(LocationShare::query(), request()->user(), LocationShare::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
