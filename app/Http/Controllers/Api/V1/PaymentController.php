<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PaymentResource::collection(TripV1Access::scope(Payment::query(), request()->user(), Payment::class)->latest('created_at')->paginate(25));
    }

    public function store(StorePaymentRequest $request): PaymentResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Payment)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new PaymentResource(Payment::create($attributes));
    }

    public function show(string $id): PaymentResource
    {
        $model = TripV1Access::scope(Payment::query(), request()->user(), Payment::class)->findOrFail($id);

        return new PaymentResource($model);
    }

    public function update(UpdatePaymentRequest $request, string $id): PaymentResource
    {
        $model = TripV1Access::scope(Payment::query(), request()->user(), Payment::class)->findOrFail($id);
        $model->update($request->validated());

        return new PaymentResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(Payment::query(), request()->user(), Payment::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
