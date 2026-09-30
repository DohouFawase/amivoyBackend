<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecommendationRequest;
use App\Http\Requests\UpdateRecommendationRequest;
use App\Http\Resources\RecommendationResource;
use App\Models\Recommendation;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RecommendationController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return RecommendationResource::collection(TripV1Access::scope(Recommendation::query(), request()->user(), Recommendation::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreRecommendationRequest $request): RecommendationResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Recommendation)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new RecommendationResource(Recommendation::create($attributes));
    }

    public function show(string $id): RecommendationResource
    {
        $model = TripV1Access::scope(Recommendation::query(), request()->user(), Recommendation::class)->findOrFail($id);

        return new RecommendationResource($model);
    }

    public function update(UpdateRecommendationRequest $request, string $id): RecommendationResource
    {
        $model = TripV1Access::scope(Recommendation::query(), request()->user(), Recommendation::class)->findOrFail($id);
        $model->update($request->validated());

        return new RecommendationResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(Recommendation::query(), request()->user(), Recommendation::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
