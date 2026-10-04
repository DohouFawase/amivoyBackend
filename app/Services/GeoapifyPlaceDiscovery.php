<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeoapifyPlaceDiscovery
{
    private const AFRICA_RECT = '-18,-35,52,38';

    public function isConfigured(): bool
    {
        return filled(config('services.geoapify.key'));
    }

    /** @return array<int, array<string, mixed>> */
    public function search(string $query, ?string $country = null, int $limit = 8): array
    {
        $key = config('services.geoapify.key');
        if (! $key) {
            return [];
        }

        $text = trim($query.($country ? ', '.$country : ''));
        $cacheKey = 'places:geoapify:search:'.sha1(mb_strtolower($text).'|'.$limit);

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($key, $text, $limit): array {
            $response = Http::timeout(12)->get('https://api.geoapify.com/v1/geocode/search', [
                'text' => $text,
                'filter' => 'rect:'.self::AFRICA_RECT,
                'lang' => 'fr',
                'limit' => min(20, max(1, $limit)),
                'format' => 'json',
                'apiKey' => $key,
            ]);
            $response->throw();

            return collect($response->json('results', []))
                ->filter(fn (array $place): bool => isset($place['lat'], $place['lon']) && filled($place['formatted'] ?? null))
                ->map(fn (array $place): array => $this->mapGeocodeResult($place))
                ->values()
                ->all();
        });
    }

    /** @return array<int, array<string, mixed>> */
    public function nearby(float $latitude, float $longitude, string $category, int $limit = 40): array
    {
        $key = config('services.geoapify.key');
        if (! $key) {
            return [];
        }

        $categories = $this->categoriesFor($category);
        $cacheKey = 'places:geoapify:nearby:'.sha1(json_encode([
            round($latitude, 4), round($longitude, 4), $categories, $limit,
        ]));

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($key, $latitude, $longitude, $categories, $limit): array {
            $point = $longitude.','.$latitude;
            $response = Http::timeout(12)->get('https://api.geoapify.com/v2/places', [
                'categories' => implode(',', $categories),
                'filter' => 'circle:'.$point.',15000',
                'bias' => 'proximity:'.$point,
                'limit' => min(100, max(1, $limit)),
                'lang' => 'fr',
                'apiKey' => $key,
            ]);
            $response->throw();

            return collect($response->json('features', []))
                ->map(function (array $feature): ?array {
                    $place = $feature['properties'] ?? [];
                    $coordinates = $feature['geometry']['coordinates'] ?? [];
                    $longitude = $place['lon'] ?? $coordinates[0] ?? null;
                    $latitude = $place['lat'] ?? $coordinates[1] ?? null;
                    if (! filled($place['name'] ?? null) || ! is_numeric($latitude) || ! is_numeric($longitude)) {
                        return null;
                    }

                    $type = collect($place['categories'] ?? [])->first(fn (string $value): bool => str_contains($value, '.')) ?? 'place';
                    $details = collect([
                        ($place['distance'] ?? null) ? round($place['distance']).' m' : null,
                        $place['opening_hours'] ?? null,
                        $place['website'] ?? null,
                    ])->filter()->implode(' · ');

                    return [
                        'id' => 'geoapify-'.($place['place_id'] ?? sha1($place['name'].$latitude.$longitude)),
                        'name' => $place['name'],
                        'category' => str_replace('.', ' · ', $type),
                        'description' => $details ?: 'Lieu référencé dans Geoapify.',
                        'address' => $place['formatted'] ?? $place['address_line2'] ?? 'Adresse non renseignée',
                        'latitude' => (float) $latitude,
                        'longitude' => (float) $longitude,
                        'source' => 'Geoapify',
                        'sourceUrl' => 'https://www.geoapify.com/',
                        'verificationStatus' => 'mapped',
                        'verified' => false,
                    ];
                })
                ->filter()
                ->values()
                ->all();
        });
    }

    /** @return array<string, mixed> */
    private function mapGeocodeResult(array $place): array
    {
        $country = $place['country'] ?? null;
        $name = $place['name'] ?? $place['city'] ?? $place['formatted'];
        $resultType = $place['result_type'] ?? $place['type'] ?? 'place';

        return [
            'id' => 'geoapify-'.($place['place_id'] ?? sha1($place['formatted'])),
            'name' => $name,
            'category' => $resultType,
            'description' => $place['formatted'],
            'address' => $place['formatted'],
            'country' => $country,
            'region' => $place['state'] ?? $place['county'] ?? null,
            'place_type' => $resultType,
            'lat' => (float) $place['lat'],
            'lng' => (float) $place['lon'],
            'latitude' => (float) $place['lat'],
            'longitude' => (float) $place['lon'],
            'emoji' => null,
            'isCountry' => $resultType === 'country',
            'source' => 'Geoapify',
        ];
    }

    /** @return array<int, string> */
    private function categoriesFor(string $category): array
    {
        $category = mb_strtolower($category);

        return match (true) {
            str_contains($category, 'restaurant') => ['catering.restaurant', 'catering.cafe', 'catering.fast_food', 'catering.bar'],
            str_contains($category, 'hébergement'), str_contains($category, 'hebergement') => ['accommodation'],
            str_contains($category, 'culture') => ['entertainment.museum', 'entertainment.culture', 'tourism.attraction'],
            str_contains($category, 'nature') => ['leisure.park', 'natural', 'beach'],
            default => ['tourism.attraction', 'entertainment', 'leisure'],
        };
    }
}
