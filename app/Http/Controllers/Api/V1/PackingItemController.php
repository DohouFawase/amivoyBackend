<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePackingItemRequest;
use App\Http\Requests\UpdatePackingItemRequest;
use App\Http\Resources\PackingItemResource;
use App\Models\PackingItem;
use App\Support\TripV1Access;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PackingItemController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $items = TripV1Access::scope(PackingItem::query(), request()->user(), PackingItem::class)
            ->orderBy('is_packed')
            ->orderBy('category')
            ->orderBy('title')
            ->paginate(25);

        return PackingItemResource::collection($items);
    }

    public function store(StorePackingItemRequest $request): PackingItemResource
    {
        $attributes = $request->validated();
        $attributes['added_by'] = $request->user()->id;

        return new PackingItemResource(PackingItem::create($attributes));
    }

    public function show(string $id): PackingItemResource
    {
        $item = TripV1Access::scope(PackingItem::query(), request()->user(), PackingItem::class)->findOrFail($id);

        return new PackingItemResource($item);
    }

    public function update(UpdatePackingItemRequest $request, string $id): PackingItemResource
    {
        $item = TripV1Access::scope(PackingItem::query(), request()->user(), PackingItem::class)->findOrFail($id);
        $item->update($request->validated());

        return new PackingItemResource($item->refresh());
    }

    public function destroy(string $id): Response
    {
        $item = TripV1Access::scope(PackingItem::query(), request()->user(), PackingItem::class)->findOrFail($id);
        $item->delete();

        return response()->noContent();
    }
}
