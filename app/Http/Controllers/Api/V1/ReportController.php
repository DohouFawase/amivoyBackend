<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ReportResource::collection(TripV1Access::scope(Report::query(), request()->user(), Report::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreReportRequest $request): ReportResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Report)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new ReportResource(Report::create($attributes));
    }

    public function show(string $id): ReportResource
    {
        $model = TripV1Access::scope(Report::query(), request()->user(), Report::class)->findOrFail($id);

        return new ReportResource($model);
    }

    public function update(UpdateReportRequest $request, string $id): ReportResource
    {
        $model = TripV1Access::scope(Report::query(), request()->user(), Report::class)->findOrFail($id);
        $model->update($request->validated());

        return new ReportResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(Report::query(), request()->user(), Report::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
