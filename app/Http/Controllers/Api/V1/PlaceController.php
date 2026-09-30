<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlaceRequest;
use App\Http\Requests\UpdatePlaceRequest;
use App\Http\Resources\PlaceResource;
use App\Models\Place;
use App\Support\TripV1Access;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

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
        $suggestions = [
            'Hébergements' => [
                ['Appartement lumineux', 'Appartement', '2 voyageurs · Wi-Fi · cuisine équipée · dès 18 000 XOF / nuit', '🏢'],
                ['Studio des voyageurs', 'Studio', 'Quartier calme · climatisation · dès 24 000 XOF / nuit', '🛏️'],
                ['Résidence avec terrasse', 'Résidence', '4 voyageurs · parking · dès 32 000 XOF / nuit', '🏡'],
            ],
            'Restaurants' => [
                ['La Terrasse du marché', 'Restaurant', 'Cuisine locale · plats à partager · 10 000–16 000 XOF', '🍲'],
                ['Café des voyageurs', 'Café', 'Petit-déjeuner · café · terrasse ombragée', '☕'],
                ['Chez Awa', 'Restaurant', 'Spécialités maison · ambiance conviviale', '🥘'],
            ],
            'Culture' => [
                ['Musée des cultures', 'Musée', 'Collections locales · visite 1 h 30', '🏛️'],
                ['Place des artisans', 'Artisanat', 'Ateliers et créations fabriquées sur place', '🧵'],
                ['Le quartier historique', 'Patrimoine', 'Architecture et histoire de la ville', '📷'],
            ],
            'Nature' => [
                ['Jardin botanique', 'Nature', 'Promenade ombragée · idéal le matin', '🌿'],
                ['La plage des pêcheurs', 'Plage', 'Coucher de soleil et pirogues colorées', '🏝️'],
                ['Balade au bord de l’eau', 'Promenade', 'Parcours facile · environ 45 minutes', '🌊'],
            ],
            'À faire' => [
                ['Visite guidée de la ville', 'Activité', 'Guide local · départ à 9 h et 15 h', '🧭'],
                ['Marché central', 'Marché', 'Saveurs, tissus et artisanat local', '🧺'],
                ['Atelier cuisine locale', 'Expérience', 'Découverte et dégustation · 2 heures', '🍋'],
            ],
        ];
        $items = $suggestions[$data['category']] ?? $suggestions['À faire'];
        $location = $data['location'] ?? 'ce quartier';

        return response()->json(['data' => array_map(
            fn (array $item, int $index): array => [
                'id' => 'demo-'.str($data['category'])->slug().'-'.$index,
                'name' => $item[0],
                'category' => $item[1],
                'description' => $item[3].' '.$item[2],
                'address' => 'À proximité de '.$location.' · adresse de démonstration',
                'latitude' => (float) $data['latitude'] + ($index - 1) * 0.018,
                'longitude' => (float) $data['longitude'] + ($index - 1) * 0.021,
                'isMock' => true,
            ], $items, array_keys($items),
        )]);
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
