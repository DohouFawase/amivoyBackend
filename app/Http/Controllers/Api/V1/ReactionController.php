<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReactionRequest;
use App\Http\Requests\UpdateReactionRequest;
use App\Http\Resources\ReactionResource;
use App\Models\Reaction;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ReactionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ReactionResource::collection(TripV1Access::scope(Reaction::query(), request()->user(), Reaction::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreReactionRequest $request): ReactionResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Reaction)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new ReactionResource(Reaction::create($attributes));
    }

    public function show(string $id): ReactionResource
    {
        $model = TripV1Access::scope(Reaction::query(), request()->user(), Reaction::class)->findOrFail($id);

        return new ReactionResource($model);
    }

    public function update(UpdateReactionRequest $request, string $id): ReactionResource
    {
        $model = TripV1Access::scope(Reaction::query(), request()->user(), Reaction::class)->findOrFail($id);
        $model->update($request->validated());

        return new ReactionResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(Reaction::query(), request()->user(), Reaction::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
