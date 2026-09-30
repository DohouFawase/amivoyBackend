<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePollAnswerRequest;
use App\Http\Requests\UpdatePollAnswerRequest;
use App\Http\Resources\PollAnswerResource;
use App\Models\PollAnswer;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PollAnswerController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PollAnswerResource::collection(TripV1Access::scope(PollAnswer::query(), request()->user(), PollAnswer::class)->latest('created_at')->paginate(25));
    }

    public function store(StorePollAnswerRequest $request): PollAnswerResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new PollAnswer)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new PollAnswerResource(PollAnswer::create($attributes));
    }

    public function show(string $id): PollAnswerResource
    {
        $model = TripV1Access::scope(PollAnswer::query(), request()->user(), PollAnswer::class)->findOrFail($id);

        return new PollAnswerResource($model);
    }

    public function update(UpdatePollAnswerRequest $request, string $id): PollAnswerResource
    {
        $model = TripV1Access::scope(PollAnswer::query(), request()->user(), PollAnswer::class)->findOrFail($id);
        $model->update($request->validated());

        return new PollAnswerResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(PollAnswer::query(), request()->user(), PollAnswer::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
