<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceTokenRequest;
use App\Http\Requests\UpdateDeviceTokenRequest;
use App\Http\Resources\DeviceTokenResource;
use App\Models\DeviceToken;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DeviceTokenController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return DeviceTokenResource::collection(TripV1Access::scope(DeviceToken::query(), request()->user(), DeviceToken::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreDeviceTokenRequest $request): DeviceTokenResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new DeviceToken)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new DeviceTokenResource(DeviceToken::create($attributes));
    }

    public function show(string $id): DeviceTokenResource
    {
        $model = TripV1Access::scope(DeviceToken::query(), request()->user(), DeviceToken::class)->findOrFail($id);

        return new DeviceTokenResource($model);
    }

    public function update(UpdateDeviceTokenRequest $request, string $id): DeviceTokenResource
    {
        $model = TripV1Access::scope(DeviceToken::query(), request()->user(), DeviceToken::class)->findOrFail($id);
        $model->update($request->validated());

        return new DeviceTokenResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(DeviceToken::query(), request()->user(), DeviceToken::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
