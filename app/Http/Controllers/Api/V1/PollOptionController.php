<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePollOptionRequest;
use App\Http\Requests\UpdatePollOptionRequest;
use App\Http\Resources\PollOptionResource;
use App\Models\PollOption;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PollOptionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PollOptionResource::collection(TripV1Access::scope(PollOption::query(), request()->user(), PollOption::class)->latest('created_at')->paginate(25));
    }

    public function store(StorePollOptionRequest $request): PollOptionResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new PollOption)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new PollOptionResource(PollOption::create($attributes));
    }

    public function show(string $id): PollOptionResource
    {
        $model = TripV1Access::scope(PollOption::query(), request()->user(), PollOption::class)->findOrFail($id);

        return new PollOptionResource($model);
    }

    public function update(UpdatePollOptionRequest $request, string $id): PollOptionResource
    {
        $model = TripV1Access::scope(PollOption::query(), request()->user(), PollOption::class)->findOrFail($id);
        $model->update($request->validated());

        return new PollOptionResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(PollOption::query(), request()->user(), PollOption::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
