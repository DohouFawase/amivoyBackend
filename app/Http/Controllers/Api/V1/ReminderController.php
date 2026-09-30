<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReminderRequest;
use App\Http\Requests\UpdateReminderRequest;
use App\Http\Resources\ReminderResource;
use App\Models\Reminder;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ReminderController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ReminderResource::collection(TripV1Access::scope(Reminder::query(), request()->user(), Reminder::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreReminderRequest $request): ReminderResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Reminder)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new ReminderResource(Reminder::create($attributes));
    }

    public function show(string $id): ReminderResource
    {
        $model = TripV1Access::scope(Reminder::query(), request()->user(), Reminder::class)->findOrFail($id);

        return new ReminderResource($model);
    }

    public function update(UpdateReminderRequest $request, string $id): ReminderResource
    {
        $model = TripV1Access::scope(Reminder::query(), request()->user(), Reminder::class)->findOrFail($id);
        $model->update($request->validated());

        return new ReminderResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(Reminder::query(), request()->user(), Reminder::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
