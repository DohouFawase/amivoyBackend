<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ServiceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ServiceResource::collection(TripV1Access::scope(Service::query(), request()->user(), Service::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreServiceRequest $request): ServiceResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Service)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new ServiceResource(Service::create($attributes));
    }

    public function show(string $id): ServiceResource
    {
        $model = TripV1Access::scope(Service::query(), request()->user(), Service::class)->findOrFail($id);

        return new ServiceResource($model);
    }

    public function update(UpdateServiceRequest $request, string $id): ServiceResource
    {
        $model = TripV1Access::scope(Service::query(), request()->user(), Service::class)->findOrFail($id);
        $model->update($request->validated());

        return new ServiceResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        abort_unless(TripV1Access::canManageGlobalResource(request()->user(), Service::class), 403);
        $model = TripV1Access::scope(Service::query(), request()->user(), Service::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
