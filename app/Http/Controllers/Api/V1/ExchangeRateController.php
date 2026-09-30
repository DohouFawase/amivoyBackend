<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExchangeRateRequest;
use App\Http\Requests\UpdateExchangeRateRequest;
use App\Http\Resources\ExchangeRateResource;
use App\Models\ExchangeRate;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ExchangeRateController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ExchangeRateResource::collection(TripV1Access::scope(ExchangeRate::query(), request()->user(), ExchangeRate::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreExchangeRateRequest $request): ExchangeRateResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new ExchangeRate)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new ExchangeRateResource(ExchangeRate::create($attributes));
    }

    public function show(string $id): ExchangeRateResource
    {
        $model = TripV1Access::scope(ExchangeRate::query(), request()->user(), ExchangeRate::class)->findOrFail($id);

        return new ExchangeRateResource($model);
    }

    public function update(UpdateExchangeRateRequest $request, string $id): ExchangeRateResource
    {
        $model = TripV1Access::scope(ExchangeRate::query(), request()->user(), ExchangeRate::class)->findOrFail($id);
        $model->update($request->validated());

        return new ExchangeRateResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        abort_unless(TripV1Access::canManageGlobalResource(request()->user(), ExchangeRate::class), 403);
        $model = TripV1Access::scope(ExchangeRate::query(), request()->user(), ExchangeRate::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
