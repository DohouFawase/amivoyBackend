<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseParticipantRequest;
use App\Http\Requests\UpdateExpenseParticipantRequest;
use App\Http\Resources\ExpenseParticipantResource;
use App\Models\ExpenseParticipant;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ExpenseParticipantController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ExpenseParticipantResource::collection(TripV1Access::scope(ExpenseParticipant::query(), request()->user(), ExpenseParticipant::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreExpenseParticipantRequest $request): ExpenseParticipantResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new ExpenseParticipant)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new ExpenseParticipantResource(ExpenseParticipant::create($attributes));
    }

    public function show(string $id): ExpenseParticipantResource
    {
        $model = TripV1Access::scope(ExpenseParticipant::query(), request()->user(), ExpenseParticipant::class)->findOrFail($id);

        return new ExpenseParticipantResource($model);
    }

    public function update(UpdateExpenseParticipantRequest $request, string $id): ExpenseParticipantResource
    {
        $model = TripV1Access::scope(ExpenseParticipant::query(), request()->user(), ExpenseParticipant::class)->findOrFail($id);
        $model->update($request->validated());

        return new ExpenseParticipantResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(ExpenseParticipant::query(), request()->user(), ExpenseParticipant::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
