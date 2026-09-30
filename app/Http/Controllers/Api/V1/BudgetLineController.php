<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBudgetLineRequest;
use App\Http\Requests\UpdateBudgetLineRequest;
use App\Http\Resources\BudgetLineResource;
use App\Models\BudgetLine;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BudgetLineController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return BudgetLineResource::collection(TripV1Access::scope(BudgetLine::query(), request()->user(), BudgetLine::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreBudgetLineRequest $request): BudgetLineResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new BudgetLine)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new BudgetLineResource(BudgetLine::create($attributes));
    }

    public function show(string $id): BudgetLineResource
    {
        $model = TripV1Access::scope(BudgetLine::query(), request()->user(), BudgetLine::class)->findOrFail($id);

        return new BudgetLineResource($model);
    }

    public function update(UpdateBudgetLineRequest $request, string $id): BudgetLineResource
    {
        $model = TripV1Access::scope(BudgetLine::query(), request()->user(), BudgetLine::class)->findOrFail($id);
        $model->update($request->validated());

        return new BudgetLineResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(BudgetLine::query(), request()->user(), BudgetLine::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
