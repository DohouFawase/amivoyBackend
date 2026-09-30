<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TripRoutePlanRequest;
use App\Models\Trip;
use App\Support\TripV1Access;
use Illuminate\Http\JsonResponse;

class TripRouteController extends Controller
{
    public function suggestOrder(TripRoutePlanRequest $request, string $trip): JsonResponse
    {
        $tripModel = TripV1Access::scope(Trip::query(), $request->user(), Trip::class)->findOrFail($trip);
        $tripPlaces = $tripModel->tripPlaces()->with('place')->whereHas('place', function ($query): void {
            $query->whereNotNull('lat')->whereNotNull('lng');
        })->get();

        $stops = $tripPlaces->map(fn ($tripPlace): ?array => $tripPlace->place === null ? null : [
            'trip_place_id' => $tripPlace->id,
            'place_id' => $tripPlace->place->id,
            'name' => $tripPlace->place->name,
            'address' => $tripPlace->place->address,
            'lat' => (float) $tripPlace->place->lat,
            'lng' => (float) $tripPlace->place->lng,
        ])->filter()->values()->all();

        $origin = $request->validated('origin_lat') !== null
            ? ['lat' => (float) $request->validated('origin_lat'), 'lng' => (float) $request->validated('origin_lng')]
            : null;

        if ($origin === null && $stops !== []) {
            $origin = $this->centralStop($stops);
        }

        $orderedStops = [];
        $totalDistance = 0.0;

        while ($stops !== []) {
            $nearestIndex = $this->nearestStopIndex($origin, $stops);
            $stop = $stops[$nearestIndex];
            $distance = $origin === null ? 0.0 : $this->distanceKm($origin, $stop);
            $stop['distance_from_previous_km'] = round($distance, 2);
            $totalDistance += $distance;
            $orderedStops[] = $stop;
            $origin = $stop;
            array_splice($stops, $nearestIndex, 1);
        }

        return response()->json([
            'trip_id' => $tripModel->id,
            'method' => 'nearest_neighbor_haversine',
            'distance_type' => 'straight_line_estimate',
            'estimated_total_distance_km' => round($totalDistance, 2),
            'stops_without_coordinates' => $tripModel->tripPlaces()->where(function ($query): void {
                $query->whereDoesntHave('place')
                    ->orWhereHas('place', function ($placeQuery): void {
                        $placeQuery->whereNull('lat')->orWhereNull('lng');
                    });
            })->count(),
            'stops' => $orderedStops,
        ]);
    }

    /** @param list<array{lat: float, lng: float}> $stops
     * @return array{lat: float, lng: float}
     */
    private function centralStop(array $stops): array
    {
        $bestStop = $stops[0];
        $shortestTotalDistance = INF;

        foreach ($stops as $candidate) {
            $totalDistance = 0.0;

            foreach ($stops as $stop) {
                $totalDistance += $this->distanceKm($candidate, $stop);
            }

            if ($totalDistance < $shortestTotalDistance) {
                $bestStop = $candidate;
                $shortestTotalDistance = $totalDistance;
            }
        }

        return $bestStop;
    }

    /** @param array{lat: float, lng: float} $origin
     * @param  list<array<string, mixed>>  $stops
     */
    private function nearestStopIndex(array $origin, array $stops): int
    {
        $nearestIndex = 0;
        $shortestDistance = INF;

        foreach ($stops as $index => $stop) {
            $distance = $this->distanceKm($origin, $stop);

            if ($distance < $shortestDistance) {
                $nearestIndex = $index;
                $shortestDistance = $distance;
            }
        }

        return $nearestIndex;
    }

    /** @param array{lat: float, lng: float} $from
     * @param  array{lat: float, lng: float}  $to
     */
    private function distanceKm(array $from, array $to): float
    {
        $latitudeDifference = deg2rad($to['lat'] - $from['lat']);
        $longitudeDifference = deg2rad($to['lng'] - $from['lng']);
        $a = sin($latitudeDifference / 2) ** 2
            + cos(deg2rad($from['lat'])) * cos(deg2rad($to['lat'])) * sin($longitudeDifference / 2) ** 2;

        $a = min(1.0, max(0.0, $a));

        return 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
