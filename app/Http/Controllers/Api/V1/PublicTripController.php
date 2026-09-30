<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiscoverTripsRequest;
use App\Http\Resources\PublicTripResource;
use App\Models\Trip;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicTripController extends Controller
{
    public function index(DiscoverTripsRequest $request): AnonymousResourceCollection
    {
        $query = Trip::query()->where('visibility', 'public');
        $search = $request->validated('q');

        if (is_string($search) && $search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('destination_label', 'like', "%{$search}%");
            });
        }

        return PublicTripResource::collection(
            $query->latest('created_at')->paginate($request->integer('per_page', 20)),
        );
    }

    public function show(DiscoverTripsRequest $request, string $id): PublicTripResource
    {
        $trip = Trip::query()
            ->where('visibility', 'public')
            ->findOrFail($id);

        return new PublicTripResource($trip);
    }
}
