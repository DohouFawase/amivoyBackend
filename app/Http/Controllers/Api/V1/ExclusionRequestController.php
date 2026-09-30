<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExclusionRequestRequest;
use App\Http\Requests\UpdateExclusionRequestRequest;
use App\Http\Resources\ExclusionRequestResource;
use App\Models\ExclusionRequest;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ExclusionRequestController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ExclusionRequestResource::collection(TripV1Access::scope(ExclusionRequest::query(), request()->user(), ExclusionRequest::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreExclusionRequestRequest $request): ExclusionRequestResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new ExclusionRequest)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new ExclusionRequestResource(ExclusionRequest::create($attributes));
    }

    public function show(string $id): ExclusionRequestResource
    {
        $model = TripV1Access::scope(ExclusionRequest::query(), request()->user(), ExclusionRequest::class)->findOrFail($id);

        return new ExclusionRequestResource($model);
    }

    public function update(UpdateExclusionRequestRequest $request, string $id): ExclusionRequestResource
    {
        $model = TripV1Access::scope(ExclusionRequest::query(), request()->user(), ExclusionRequest::class)->findOrFail($id);
        $model->update($request->validated());

        return new ExclusionRequestResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(ExclusionRequest::query(), request()->user(), ExclusionRequest::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
