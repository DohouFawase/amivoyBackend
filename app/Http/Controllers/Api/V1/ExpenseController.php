<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ExpenseController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ExpenseResource::collection(TripV1Access::scope(Expense::query(), request()->user(), Expense::class)->with('paid_by.user')->latest('created_at')->paginate(25));
    }

    public function store(StoreExpenseRequest $request): ExpenseResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Expense)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new ExpenseResource(Expense::create($attributes)->load('paid_by.user'));
    }

    public function show(string $id): ExpenseResource
    {
        $model = TripV1Access::scope(Expense::query(), request()->user(), Expense::class)->findOrFail($id);

        return new ExpenseResource($model->load('paid_by.user'));
    }

    public function update(UpdateExpenseRequest $request, string $id): ExpenseResource
    {
        $model = TripV1Access::scope(Expense::query(), request()->user(), Expense::class)->findOrFail($id);
        $model->update($request->validated());

        return new ExpenseResource($model->refresh()->load('paid_by.user'));
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(Expense::query(), request()->user(), Expense::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
