<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePhotoCommentRequest;
use App\Http\Requests\UpdatePhotoCommentRequest;
use App\Http\Resources\PhotoCommentResource;
use App\Models\PhotoComment;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PhotoCommentController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PhotoCommentResource::collection(TripV1Access::scope(PhotoComment::query(), request()->user(), PhotoComment::class)->latest('created_at')->paginate(25));
    }

    public function store(StorePhotoCommentRequest $request): PhotoCommentResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new PhotoComment)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new PhotoCommentResource(PhotoComment::create($attributes));
    }

    public function show(string $id): PhotoCommentResource
    {
        $model = TripV1Access::scope(PhotoComment::query(), request()->user(), PhotoComment::class)->findOrFail($id);

        return new PhotoCommentResource($model);
    }

    public function update(UpdatePhotoCommentRequest $request, string $id): PhotoCommentResource
    {
        $model = TripV1Access::scope(PhotoComment::query(), request()->user(), PhotoComment::class)->findOrFail($id);
        $model->update($request->validated());

        return new PhotoCommentResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(PhotoComment::query(), request()->user(), PhotoComment::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
