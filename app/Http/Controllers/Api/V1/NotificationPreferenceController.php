<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationPreferenceRequest;
use App\Http\Requests\UpdateNotificationPreferenceRequest;
use App\Http\Resources\NotificationPreferenceResource;
use App\Models\NotificationPreference;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class NotificationPreferenceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return NotificationPreferenceResource::collection(TripV1Access::scope(NotificationPreference::query(), request()->user(), NotificationPreference::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreNotificationPreferenceRequest $request): NotificationPreferenceResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new NotificationPreference)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new NotificationPreferenceResource(NotificationPreference::create($attributes));
    }

    public function show(string $id): NotificationPreferenceResource
    {
        $model = TripV1Access::scope(NotificationPreference::query(), request()->user(), NotificationPreference::class)->findOrFail($id);

        return new NotificationPreferenceResource($model);
    }

    public function update(UpdateNotificationPreferenceRequest $request, string $id): NotificationPreferenceResource
    {
        $model = TripV1Access::scope(NotificationPreference::query(), request()->user(), NotificationPreference::class)->findOrFail($id);
        $model->update($request->validated());

        return new NotificationPreferenceResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(NotificationPreference::query(), request()->user(), NotificationPreference::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
