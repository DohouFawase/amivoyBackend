<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePartnerRequest;
use App\Http\Requests\UpdatePartnerRequest;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PartnerController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PartnerResource::collection(TripV1Access::scope(Partner::query(), request()->user(), Partner::class)->latest('created_at')->paginate(25));
    }

    public function store(StorePartnerRequest $request): PartnerResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Partner)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new PartnerResource(Partner::create($attributes));
    }

    public function show(string $id): PartnerResource
    {
        $model = TripV1Access::scope(Partner::query(), request()->user(), Partner::class)->findOrFail($id);

        return new PartnerResource($model);
    }

    public function update(UpdatePartnerRequest $request, string $id): PartnerResource
    {
        $model = TripV1Access::scope(Partner::query(), request()->user(), Partner::class)->findOrFail($id);
        $model->update($request->validated());

        return new PartnerResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        abort_unless(TripV1Access::canManageGlobalResource(request()->user(), Partner::class), 403);
        $model = TripV1Access::scope(Partner::query(), request()->user(), Partner::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
