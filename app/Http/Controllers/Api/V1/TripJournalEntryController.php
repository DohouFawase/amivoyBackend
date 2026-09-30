<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTripJournalEntryRequest;
use App\Http\Requests\UpdateTripJournalEntryRequest;
use App\Http\Resources\TripJournalEntryResource;
use App\Models\TripJournalEntry;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TripJournalEntryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $entries = TripV1Access::scope(TripJournalEntry::query(), request()->user(), TripJournalEntry::class)
            ->latest('happened_at')
            ->latest('created_at')
            ->paginate(25);

        return TripJournalEntryResource::collection($entries);
    }

    public function store(StoreTripJournalEntryRequest $request): TripJournalEntryResource
    {
        $entry = TripJournalEntry::create([
            ...$request->validated(),
            'author_id' => $request->user()->id,
        ]);

        return new TripJournalEntryResource($entry);
    }

    public function show(string $id): TripJournalEntryResource
    {
        $entry = TripV1Access::scope(TripJournalEntry::query(), request()->user(), TripJournalEntry::class)->findOrFail($id);

        return new TripJournalEntryResource($entry);
    }

    public function update(UpdateTripJournalEntryRequest $request, string $id): TripJournalEntryResource
    {
        $entry = TripV1Access::scope(TripJournalEntry::query(), request()->user(), TripJournalEntry::class)->findOrFail($id);
        $entry->update($request->validated());

        return new TripJournalEntryResource($entry->refresh());
    }

    public function destroy(string $id): Response
    {
        $entry = TripV1Access::scope(TripJournalEntry::query(), request()->user(), TripJournalEntry::class)->findOrFail($id);
        $entry->delete();

        return response()->noContent();
    }
}
