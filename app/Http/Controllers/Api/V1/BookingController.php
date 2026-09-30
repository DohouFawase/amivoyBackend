<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BookingController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return BookingResource::collection(TripV1Access::scope(Booking::query(), request()->user(), Booking::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreBookingRequest $request): BookingResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Booking)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new BookingResource(Booking::create($attributes));
    }

    public function show(string $id): BookingResource
    {
        $model = TripV1Access::scope(Booking::query(), request()->user(), Booking::class)->findOrFail($id);

        return new BookingResource($model);
    }

    public function update(UpdateBookingRequest $request, string $id): BookingResource
    {
        $model = TripV1Access::scope(Booking::query(), request()->user(), Booking::class)->findOrFail($id);
        $model->update($request->validated());

        return new BookingResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(Booking::query(), request()->user(), Booking::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
