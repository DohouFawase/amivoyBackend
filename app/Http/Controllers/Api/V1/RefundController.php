<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRefundRequest;
use App\Http\Requests\UpdateRefundRequest;
use App\Http\Resources\RefundResource;
use App\Models\Refund;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RefundController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return RefundResource::collection(TripV1Access::scope(Refund::query(), request()->user(), Refund::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreRefundRequest $request): RefundResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Refund)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new RefundResource(Refund::create($attributes));
    }

    public function show(string $id): RefundResource
    {
        $model = TripV1Access::scope(Refund::query(), request()->user(), Refund::class)->findOrFail($id);

        return new RefundResource($model);
    }

    public function update(UpdateRefundRequest $request, string $id): RefundResource
    {
        $model = TripV1Access::scope(Refund::query(), request()->user(), Refund::class)->findOrFail($id);
        $model->update($request->validated());

        return new RefundResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(Refund::query(), request()->user(), Refund::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
