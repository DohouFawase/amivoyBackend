<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlaceDiscoveryAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_external_place_search_failure_returns_a_retryable_error(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        Http::preventStrayRequests();
        Http::fake([
            'https://overpass-api.de/api/interpreter' => Http::response([], 504),
        ]);

        $this->getJson('/api/v1/places/nearby?latitude=6.37&longitude=2.42&category=restaurant')
            ->assertServiceUnavailable()
            ->assertJsonPath('message', 'La recherche de lieux est temporairement indisponible. Réessaie dans un instant.')
            ->assertHeader('Retry-After', '60');
    }
}
