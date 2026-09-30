<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDestinationProposalRequest;
use App\Http\Requests\UpdateDestinationProposalRequest;
use App\Http\Resources\DestinationProposalResource;
use App\Models\DestinationProposal;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DestinationProposalController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return DestinationProposalResource::collection(TripV1Access::scope(DestinationProposal::query(), request()->user(), DestinationProposal::class)->latest('created_at')->paginate(25));
    }

    public function store(StoreDestinationProposalRequest $request): DestinationProposalResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new DestinationProposal)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new DestinationProposalResource(DestinationProposal::create($attributes));
    }

    public function show(string $id): DestinationProposalResource
    {
        $model = TripV1Access::scope(DestinationProposal::query(), request()->user(), DestinationProposal::class)->findOrFail($id);

        return new DestinationProposalResource($model);
    }

    public function update(UpdateDestinationProposalRequest $request, string $id): DestinationProposalResource
    {
        $model = TripV1Access::scope(DestinationProposal::query(), request()->user(), DestinationProposal::class)->findOrFail($id);
        $model->update($request->validated());

        return new DestinationProposalResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(DestinationProposal::query(), request()->user(), DestinationProposal::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
