<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_requests_are_limited_per_visitor(): void
    {
        $firstVisitor = User::factory()->create();
        $secondVisitor = User::factory()->create();

        Sanctum::actingAs($firstVisitor);
        for ($request = 0; $request < 120; $request++) {
            $this->getJson('/api/v1/me')->assertOk();
        }

        Sanctum::actingAs($secondVisitor);
        $this->getJson('/api/v1/me')->assertOk();

        Sanctum::actingAs($firstVisitor);
        $this->getJson('/api/v1/me')->assertTooManyRequests();
    }
}
