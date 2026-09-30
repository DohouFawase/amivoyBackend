<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PlanController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PlanResource::collection(TripV1Access::scope(Plan::query(), request()->user(), Plan::class)->latest('created_at')->paginate(25));
    }

    public function store(StorePlanRequest $request): PlanResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Plan)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new PlanResource(Plan::create($attributes));
    }

    public function show(string $id): PlanResource
    {
        $model = TripV1Access::scope(Plan::query(), request()->user(), Plan::class)->findOrFail($id);

        return new PlanResource($model);
    }

    public function update(UpdatePlanRequest $request, string $id): PlanResource
    {
        $model = TripV1Access::scope(Plan::query(), request()->user(), Plan::class)->findOrFail($id);
        $model->update($request->validated());

        return new PlanResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        abort_unless(TripV1Access::canManageGlobalResource(request()->user(), Plan::class), 403);
        $model = TripV1Access::scope(Plan::query(), request()->user(), Plan::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
