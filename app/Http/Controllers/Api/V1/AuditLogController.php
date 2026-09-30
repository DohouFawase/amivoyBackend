<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAuditLogRequest;
use App\Http\Requests\UpdateAuditLogRequest;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AuditLogController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return AuditLogResource::collection(TripV1Access::scope(AuditLog::query(), request()->user(), AuditLog::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreAuditLogRequest $request): AuditLogResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new AuditLog)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new AuditLogResource(AuditLog::create($attributes));
    }

    public function show(string $id): AuditLogResource
    {
        $model = TripV1Access::scope(AuditLog::query(), request()->user(), AuditLog::class)->findOrFail($id);

        return new AuditLogResource($model);
    }

    public function update(UpdateAuditLogRequest $request, string $id): AuditLogResource
    {
        $model = TripV1Access::scope(AuditLog::query(), request()->user(), AuditLog::class)->findOrFail($id);
        $model->update($request->validated());

        return new AuditLogResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(AuditLog::query(), request()->user(), AuditLog::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
