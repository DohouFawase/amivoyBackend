<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTripPlaceRequest;
use App\Http\Requests\UpdateTripPlaceRequest;
use App\Http\Resources\TripPlaceResource;
use App\Models\TripPlace;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TripPlaceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TripPlaceResource::collection(TripV1Access::scope(TripPlace::query(), request()->user(), TripPlace::class)->with(['place', 'activities'])->latest('created_at')->paginate(25));
    }

    public function store(StoreTripPlaceRequest $request): TripPlaceResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new TripPlace)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new TripPlaceResource(TripPlace::create($attributes)->load(['place', 'activities']));
    }

    public function show(string $id): TripPlaceResource
    {
        $model = TripV1Access::scope(TripPlace::query(), request()->user(), TripPlace::class)->findOrFail($id);

        return new TripPlaceResource($model->load(['place', 'activities']));
    }

    public function update(UpdateTripPlaceRequest $request, string $id): TripPlaceResource
    {
        $model = TripV1Access::scope(TripPlace::query(), request()->user(), TripPlace::class)->findOrFail($id);
        $model->update($request->validated());

        return new TripPlaceResource($model->refresh()->load(['place', 'activities']));
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(TripPlace::query(), request()->user(), TripPlace::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
