<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\UpdateBudgetRequest;
use App\Http\Resources\BudgetResource;
use App\Models\Budget;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BudgetController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return BudgetResource::collection(TripV1Access::scope(Budget::query(), request()->user(), Budget::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreBudgetRequest $request): BudgetResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Budget)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new BudgetResource(Budget::create($attributes));
    }

    public function show(string $id): BudgetResource
    {
        $model = TripV1Access::scope(Budget::query(), request()->user(), Budget::class)->findOrFail($id);

        return new BudgetResource($model);
    }

    public function update(UpdateBudgetRequest $request, string $id): BudgetResource
    {
        $model = TripV1Access::scope(Budget::query(), request()->user(), Budget::class)->findOrFail($id);
        $model->update($request->validated());

        return new BudgetResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(Budget::query(), request()->user(), Budget::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
