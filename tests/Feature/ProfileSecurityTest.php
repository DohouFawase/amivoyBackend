<?php

namespace Tests\Feature;

use App\Models\Notification as TripNotificationRecord;
use App\Models\Place;
use App\Models\User;
use App\Notifications\AuthCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_update_only_changes_allowed_fields(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/me', [
            'first_name' => 'Awa',
            'last_name' => 'Kone',
            'platform_role' => 'admin',
            'status' => 'suspended',
            'email_verified' => false,
            'password_hash' => 'attacker-controlled',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.first_name', 'Awa')
            ->assertJsonPath('data.last_name', 'Kone')
            ->assertJsonMissingPath('data.firstName')
            ->assertJsonMissingPath('data.lastName')
            ->assertJsonMissingPath('data.password_hash');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Awa',
            'last_name' => 'Kone',
            'platform_role' => 'user',
            'status' => 'active',
            'email_verified' => true,
        ]);
    }

    public function test_email_change_requires_password_and_revokes_existing_tokens(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/me/email', [
            'email' => 'new-address@example.test',
            'current_password' => 'password',
        ])->assertAccepted()
            ->assertJsonPath('email_verification_required', true);

        $user->refresh();
        $this->assertSame('new-address@example.test', $user->email);
        $this->assertFalse($user->email_verified);
        $this->assertSame(1, $user->jwt_version);
        Notification::assertSentTo($user, AuthCodeNotification::class);
    }

    public function test_avatar_upload_rejects_non_image_files(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->post('/api/v1/me/avatar', [
            'avatar' => UploadedFile::fake()->create('script.svg', 20, 'image/svg+xml'),
        ])->assertUnprocessable();
    }

    public function test_non_admin_users_cannot_delete_global_places(): void
    {
        $user = User::factory()->create();
        $place = Place::query()->create(['name' => 'Protected place']);
        Sanctum::actingAs($user);

        $this->deleteJson('/api/v1/places/'.$place->id)->assertForbidden();
        $this->assertDatabaseHas('places', ['id' => $place->id, 'deleted_at' => null]);
    }

    public function test_disabled_accounts_cannot_use_authenticated_api_routes(): void
    {
        $user = User::factory()->create(['status' => 'suspended']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/me')->assertForbidden();
    }

    public function test_users_cannot_read_another_users_notifications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $notification = TripNotificationRecord::query()->create([
            'user_id' => $otherUser->id,
            'title' => 'Private notification',
            'body' => 'Only for its owner',
        ]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/notifications/'.$notification->id)->assertNotFound();
    }
}
