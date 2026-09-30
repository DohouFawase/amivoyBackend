<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContributionRequest;
use App\Http\Requests\UpdateContributionRequest;
use App\Http\Resources\ContributionResource;
use App\Models\Contribution;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ContributionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ContributionResource::collection(TripV1Access::scope(Contribution::query(), request()->user(), Contribution::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreContributionRequest $request): ContributionResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Contribution)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new ContributionResource(Contribution::create($attributes));
    }

    public function show(string $id): ContributionResource
    {
        $model = TripV1Access::scope(Contribution::query(), request()->user(), Contribution::class)->findOrFail($id);

        return new ContributionResource($model);
    }

    public function update(UpdateContributionRequest $request, string $id): ContributionResource
    {
        $model = TripV1Access::scope(Contribution::query(), request()->user(), Contribution::class)->findOrFail($id);
        $model->update($request->validated());

        return new ContributionResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(Contribution::query(), request()->user(), Contribution::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
