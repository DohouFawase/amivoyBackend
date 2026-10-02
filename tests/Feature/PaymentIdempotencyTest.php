<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeating_a_payment_request_with_the_same_key_returns_the_existing_payment(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $payload = [
            'user_id' => $user->id,
            'amount' => 5000,
            'currency' => 'XOF',
            'status' => 'pending',
            'idempotency_key' => 'mobile-payment-attempt-01',
        ];

        $firstResponse = $this->postJson('/api/v1/payments', $payload)->assertOk();
        $secondResponse = $this->postJson('/api/v1/payments', $payload)->assertOk();

        $this->assertSame($firstResponse->json('data.id'), $secondResponse->json('data.id'));
        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'amount' => 5000,
            'idempotency_key' => 'mobile-payment-attempt-01',
        ]);
    }

    public function test_reusing_a_payment_key_with_a_different_amount_returns_conflict(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $payload = [
            'user_id' => $user->id,
            'amount' => 5000,
            'idempotency_key' => 'mobile-payment-attempt-02',
        ];

        $this->postJson('/api/v1/payments', $payload)->assertOk();
        $this->postJson('/api/v1/payments', [...$payload, 'amount' => 7500])
            ->assertConflict()
            ->assertJsonPath('message', 'Cette clé d’idempotence a déjà été utilisée avec un autre paiement.');

        $this->assertDatabaseCount('payments', 1);
    }
}
