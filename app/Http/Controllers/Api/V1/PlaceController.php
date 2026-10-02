<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlaceRequest;
use App\Http\Requests\UpdatePlaceRequest;
use App\Http\Resources\PlaceResource;
use App\Models\Place;
use App\Support\TripV1Access;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlaceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PlaceResource::collection(TripV1Access::scope(Place::query(), request()->user(), Place::class)->latest('created_at')->paginate(25));
    }

    public function search(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:120'],
            'country' => ['sometimes', 'nullable', 'string', 'max:120'],
            'limit' => ['sometimes', 'integer', 'between:1,50'],
        ]);
        $query = Place::query()->where(function (Builder $query) use ($filters): void {
            $term = '%'.addcslashes($filters['q'], '%_\\').'%';
            $query->where('name', 'like', $term)
                ->orWhere('address', 'like', $term)
                ->orWhere('region', 'like', $term)
                ->orWhere('country', 'like', $term);
        });

        if (! empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }

        return PlaceResource::collection($query->orderBy('name')->limit($filters['limit'] ?? 20)->get());
    }

    public function nearby(Request $request): JsonResponse
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'category' => ['required', 'string', 'max:80'],
            'location' => ['sometimes', 'string', 'max:120'],
        ]);
        $category = mb_strtolower($data['category']);
        $filters = match (true) {
            str_contains($category, 'restaurant') => ['amenity' => ['restaurant', 'cafe', 'fast_food', 'bar', 'pub']],
            str_contains($category, 'hébergement'), str_contains($category, 'hebergement') => ['tourism' => ['hotel', 'hostel', 'guest_house', 'apartment']],
            str_contains($category, 'culture') => ['tourism' => ['museum', 'gallery', 'attraction']],
            str_contains($category, 'nature') => ['leisure' => ['park', 'garden', 'nature_reserve']],
            default => ['tourism' => ['attraction', 'museum', 'viewpoint']],
        };
        $cacheKey = 'places:osm:'.sha1(json_encode([
            round((float) $data['latitude'], 3), round((float) $data['longitude'], 3), $filters,
        ]));

        try {
            $items = Cache::remember($cacheKey, now()->addHours(12), function () use ($data, $filters): array {
                $conditions = [];
                foreach ($filters as $key => $values) {
                    foreach ($values as $value) {
                        $conditions[] = 'node["'.$key.'"="'.$value.'"](around:5000,'.$data['latitude'].','.$data['longitude'].');';
                        $conditions[] = 'way["'.$key.'"="'.$value.'"](around:5000,'.$data['latitude'].','.$data['longitude'].');';
                        $conditions[] = 'relation["'.$key.'"="'.$value.'"](around:5000,'.$data['latitude'].','.$data['longitude'].');';
                    }
                }
                $query = '[out:json][timeout:12];('.implode('', $conditions).');out center tags 40;';
                $response = Http::timeout(15)
                    ->withHeaders(['User-Agent' => 'AmigoApp/1.0 (place discovery; contact the Amigo team)'])
                    ->asForm()
                    ->post(config('services.openstreetmap.overpass_url'), ['data' => $query]);
                $response->throw();

                return collect($response->json('elements', []))
                    ->filter(fn (array $place): bool => filled($place['tags']['name'] ?? null))
                    ->map(function (array $place): ?array {
                        $tags = $place['tags'];
                        $latitude = $place['lat'] ?? $place['center']['lat'] ?? null;
                        $longitude = $place['lon'] ?? $place['center']['lon'] ?? null;
                        if ($latitude === null || $longitude === null) {
                            return null;
                        }
                        $address = collect([
                            $tags['addr:housenumber'] ?? null,
                            $tags['addr:street'] ?? null,
                            $tags['addr:suburb'] ?? null,
                            $tags['addr:city'] ?? null,
                        ])->filter()->implode(', ');
                        $type = $tags['amenity'] ?? $tags['tourism'] ?? $tags['leisure'] ?? 'place';

                        return [
                            'id' => 'osm-'.$place['type'].'-'.$place['id'],
                            'name' => $tags['name'],
                            'category' => str_replace('_', ' ', ucfirst($type)),
                            'description' => $tags['cuisine'] ?? $tags['description'] ?? 'Établissement référencé dans OpenStreetMap.',
                            'address' => $address ?: 'Adresse non renseignée dans OpenStreetMap',
                            'latitude' => (float) $latitude,
                            'longitude' => (float) $longitude,
                            'source' => 'OpenStreetMap',
                            'sourceUrl' => 'https://www.openstreetmap.org/'.$place['type'].'/'.$place['id'],
                            'verificationStatus' => 'mapped',
                            'verified' => false,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();
            });
        } catch (ConnectionException|RequestException $exception) {
            Log::warning('OpenStreetMap place discovery failed.', [
                'exception' => $exception::class,
                'upstream_status' => $exception instanceof RequestException ? $exception->response->status() : null,
            ]);

            return response()->json([
                'message' => 'La recherche de lieux est temporairement indisponible. Réessaie dans un instant.',
            ], 503, ['Retry-After' => '60']);
        }

        return response()->json(['data' => $items, 'meta' => ['source' => 'OpenStreetMap', 'attribution' => '© OpenStreetMap contributors']]);
    }

    public function store(StorePlaceRequest $request): PlaceResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new Place)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new PlaceResource(Place::create($attributes));
    }

    public function show(string $id): PlaceResource
    {
        $model = TripV1Access::scope(Place::query(), request()->user(), Place::class)->findOrFail($id);

        return new PlaceResource($model);
    }

    public function update(UpdatePlaceRequest $request, string $id): PlaceResource
    {
        $model = TripV1Access::scope(Place::query(), request()->user(), Place::class)->findOrFail($id);
        $model->update($request->validated());

        return new PlaceResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        abort_unless(TripV1Access::canManageGlobalResource(request()->user(), Place::class), 403);
        $model = TripV1Access::scope(Place::query(), request()->user(), Place::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
