<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreModerationActionRequest;
use App\Http\Requests\UpdateModerationActionRequest;
use App\Http\Resources\ModerationActionResource;
use App\Models\ModerationAction;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ModerationActionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ModerationActionResource::collection(TripV1Access::scope(ModerationAction::query(), request()->user(), ModerationAction::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreModerationActionRequest $request): ModerationActionResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new ModerationAction)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new ModerationActionResource(ModerationAction::create($attributes));
    }

    public function show(string $id): ModerationActionResource
    {
        $model = TripV1Access::scope(ModerationAction::query(), request()->user(), ModerationAction::class)->findOrFail($id);

        return new ModerationActionResource($model);
    }

    public function update(UpdateModerationActionRequest $request, string $id): ModerationActionResource
    {
        $model = TripV1Access::scope(ModerationAction::query(), request()->user(), ModerationAction::class)->findOrFail($id);
        $model->update($request->validated());

        return new ModerationActionResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(ModerationAction::query(), request()->user(), ModerationAction::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
