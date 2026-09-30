<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SubscriptionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return SubscriptionResource::collection(TripV1Access::scope(Subscription::query(), request()->user(), Subscription::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreSubscriptionRequest $request): SubscriptionResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Subscription)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new SubscriptionResource(Subscription::create($attributes));
    }

    public function show(string $id): SubscriptionResource
    {
        $model = TripV1Access::scope(Subscription::query(), request()->user(), Subscription::class)->findOrFail($id);

        return new SubscriptionResource($model);
    }

    public function update(UpdateSubscriptionRequest $request, string $id): SubscriptionResource
    {
        $model = TripV1Access::scope(Subscription::query(), request()->user(), Subscription::class)->findOrFail($id);
        $model->update($request->validated());

        return new SubscriptionResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(Subscription::query(), request()->user(), Subscription::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
