<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentEventRequest;
use App\Http\Requests\UpdatePaymentEventRequest;
use App\Http\Resources\PaymentEventResource;
use App\Models\PaymentEvent;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PaymentEventController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PaymentEventResource::collection(TripV1Access::scope(PaymentEvent::query(), request()->user(), PaymentEvent::class)->latest('created_at')->paginate(25));
    }

    public function store(StorePaymentEventRequest $request): PaymentEventResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new PaymentEvent)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new PaymentEventResource(PaymentEvent::create($attributes));
    }

    public function show(string $id): PaymentEventResource
    {
        $model = TripV1Access::scope(PaymentEvent::query(), request()->user(), PaymentEvent::class)->findOrFail($id);

        return new PaymentEventResource($model);
    }

    public function update(UpdatePaymentEventRequest $request, string $id): PaymentEventResource
    {
        $model = TripV1Access::scope(PaymentEvent::query(), request()->user(), PaymentEvent::class)->findOrFail($id);
        $model->update($request->validated());

        return new PaymentEventResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(PaymentEvent::query(), request()->user(), PaymentEvent::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
