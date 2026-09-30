<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOfflineSyncQueueRequest;
use App\Http\Requests\UpdateOfflineSyncQueueRequest;
use App\Http\Resources\OfflineSyncQueueResource;
use App\Models\OfflineSyncQueue;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class OfflineSyncQueueController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return OfflineSyncQueueResource::collection(TripV1Access::scope(OfflineSyncQueue::query(), request()->user(), OfflineSyncQueue::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreOfflineSyncQueueRequest $request): OfflineSyncQueueResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new OfflineSyncQueue)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new OfflineSyncQueueResource(OfflineSyncQueue::create($attributes));
    }

    public function show(string $id): OfflineSyncQueueResource
    {
        $model = TripV1Access::scope(OfflineSyncQueue::query(), request()->user(), OfflineSyncQueue::class)->findOrFail($id);

        return new OfflineSyncQueueResource($model);
    }

    public function update(UpdateOfflineSyncQueueRequest $request, string $id): OfflineSyncQueueResource
    {
        $model = TripV1Access::scope(OfflineSyncQueue::query(), request()->user(), OfflineSyncQueue::class)->findOrFail($id);
        $model->update($request->validated());

        return new OfflineSyncQueueResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(OfflineSyncQueue::query(), request()->user(), OfflineSyncQueue::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
