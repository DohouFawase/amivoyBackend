<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Support\TripV1Access;
use Illuminate\Database\QueryException;
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

        $idempotencyKey = $attributes['idempotency_key'] ?? null;
        if ($idempotencyKey !== null) {
            $existingPayment = TripV1Access::scope(Payment::query(), $request->user(), Payment::class)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingPayment !== null) {
                $this->assertSameIdempotentPayload($existingPayment, $attributes);

                return new PaymentResource($existingPayment);
            }
        }

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Payment)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        try {
            return new PaymentResource(Payment::create($attributes));
        } catch (QueryException $exception) {
            if ($idempotencyKey === null) {
                throw $exception;
            }

            $existingPayment = TripV1Access::scope(Payment::query(), $request->user(), Payment::class)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingPayment === null) {
                throw $exception;
            }

            $this->assertSameIdempotentPayload($existingPayment, $attributes);

            return new PaymentResource($existingPayment);
        }
    }

    /** @param array<string, mixed> $attributes */
    private function assertSameIdempotentPayload(Payment $payment, array $attributes): void
    {
        foreach ($attributes as $attribute => $value) {
            if ($attribute === 'idempotency_key') {
                continue;
            }

            if ($payment->getAttribute($attribute) != $value) {
                abort(409, 'Cette clé d’idempotence a déjà été utilisée avec un autre paiement.');
            }
        }
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
