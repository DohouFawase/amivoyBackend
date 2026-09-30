<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePollRequest;
use App\Http\Requests\UpdatePollRequest;
use App\Http\Resources\PollResource;
use App\Models\Poll;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PollController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PollResource::collection(TripV1Access::scope(Poll::query(), request()->user(), Poll::class)->latest('created_at')->paginate(25));
    }

    public function store(StorePollRequest $request): PollResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Poll)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new PollResource(Poll::create($attributes));
    }

    public function show(string $id): PollResource
    {
        $model = TripV1Access::scope(Poll::query(), request()->user(), Poll::class)->findOrFail($id);

        return new PollResource($model);
    }

    public function update(UpdatePollRequest $request, string $id): PollResource
    {
        $model = TripV1Access::scope(Poll::query(), request()->user(), Poll::class)->findOrFail($id);
        $model->update($request->validated());

        return new PollResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(Poll::query(), request()->user(), Poll::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
