<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePhotoRequest;
use App\Http\Requests\UpdatePhotoRequest;
use App\Http\Resources\PhotoResource;
use App\Models\Photo;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PhotoController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PhotoResource::collection(TripV1Access::scope(Photo::query(), request()->user(), Photo::class)->latest('created_at')->paginate(25));
    }

    public function store(StorePhotoRequest $request): PhotoResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Photo)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new PhotoResource(Photo::create($attributes));
    }

    public function show(string $id): PhotoResource
    {
        $model = TripV1Access::scope(Photo::query(), request()->user(), Photo::class)->findOrFail($id);

        return new PhotoResource($model);
    }

    public function update(UpdatePhotoRequest $request, string $id): PhotoResource
    {
        $model = TripV1Access::scope(Photo::query(), request()->user(), Photo::class)->findOrFail($id);
        $model->update($request->validated());

        return new PhotoResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(Photo::query(), request()->user(), Photo::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
