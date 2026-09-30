<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmergencyAlertRequest;
use App\Http\Requests\UpdateEmergencyAlertRequest;
use App\Http\Resources\EmergencyAlertResource;
use App\Models\EmergencyAlert;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class EmergencyAlertController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return EmergencyAlertResource::collection(TripV1Access::scope(EmergencyAlert::query(), request()->user(), EmergencyAlert::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreEmergencyAlertRequest $request): EmergencyAlertResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new EmergencyAlert)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new EmergencyAlertResource(EmergencyAlert::create($attributes));
    }

    public function show(string $id): EmergencyAlertResource
    {
        $model = TripV1Access::scope(EmergencyAlert::query(), request()->user(), EmergencyAlert::class)->findOrFail($id);

        return new EmergencyAlertResource($model);
    }

    public function update(UpdateEmergencyAlertRequest $request, string $id): EmergencyAlertResource
    {
        $model = TripV1Access::scope(EmergencyAlert::query(), request()->user(), EmergencyAlert::class)->findOrFail($id);
        $model->update($request->validated());

        return new EmergencyAlertResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(EmergencyAlert::query(), request()->user(), EmergencyAlert::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
