<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSettlementRequest;
use App\Http\Requests\UpdateSettlementRequest;
use App\Http\Resources\SettlementResource;
use App\Models\Settlement;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SettlementController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return SettlementResource::collection(TripV1Access::scope(Settlement::query(), request()->user(), Settlement::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreSettlementRequest $request): SettlementResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Settlement)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new SettlementResource(Settlement::create($attributes));
    }

    public function show(string $id): SettlementResource
    {
        $model = TripV1Access::scope(Settlement::query(), request()->user(), Settlement::class)->findOrFail($id);

        return new SettlementResource($model);
    }

    public function update(UpdateSettlementRequest $request, string $id): SettlementResource
    {
        $model = TripV1Access::scope(Settlement::query(), request()->user(), Settlement::class)->findOrFail($id);
        $model->update($request->validated());

        return new SettlementResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(Settlement::query(), request()->user(), Settlement::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
