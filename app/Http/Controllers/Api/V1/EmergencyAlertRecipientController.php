<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmergencyAlertRecipientRequest;
use App\Http\Requests\UpdateEmergencyAlertRecipientRequest;
use App\Http\Resources\EmergencyAlertRecipientResource;
use App\Models\EmergencyAlertRecipient;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class EmergencyAlertRecipientController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return EmergencyAlertRecipientResource::collection(TripV1Access::scope(EmergencyAlertRecipient::query(), request()->user(), EmergencyAlertRecipient::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreEmergencyAlertRecipientRequest $request): EmergencyAlertRecipientResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new EmergencyAlertRecipient)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new EmergencyAlertRecipientResource(EmergencyAlertRecipient::create($attributes));
    }

    public function show(string $id): EmergencyAlertRecipientResource
    {
        $model = TripV1Access::scope(EmergencyAlertRecipient::query(), request()->user(), EmergencyAlertRecipient::class)->findOrFail($id);

        return new EmergencyAlertRecipientResource($model);
    }

    public function update(UpdateEmergencyAlertRecipientRequest $request, string $id): EmergencyAlertRecipientResource
    {
        $model = TripV1Access::scope(EmergencyAlertRecipient::query(), request()->user(), EmergencyAlertRecipient::class)->findOrFail($id);
        $model->update($request->validated());

        return new EmergencyAlertRecipientResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(EmergencyAlertRecipient::query(), request()->user(), EmergencyAlertRecipient::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
