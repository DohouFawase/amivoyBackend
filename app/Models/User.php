<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'email',
        'phone',
        'password_hash',
        'first_name',
        'last_name',
        'avatar_url',
        'language',
        'country',
        'interests',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
            'email_verified' => 'boolean',
            'phone_verified' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'interests' => 'array',
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function getJWTIdentifier(): string
    {
        return (string) $this->getKey();
    }

    /** @return array<string, mixed> */
    public function getJWTCustomClaims(): array
    {
        return ['ver' => (int) $this->jwt_version];
    }

    /** @return array<int, string> */
    public function routeNotificationForFcm(): array
    {
        return $this->deviceTokens()->pluck('fcm_apns_token')->all();
    }

    public function userSessions(): HasMany
    {
        return $this->hasMany(UserSession::class, 'user_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'creator_id');
    }

    public function tripMembers(): HasMany
    {
        return $this->hasMany(TripMember::class, 'user_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'invited_by');
    }

    public function polls(): HasMany
    {
        return $this->hasMany(Poll::class, 'created_by');
    }

    public function meetingPoints(): HasMany
    {
        return $this->hasMany(MeetingPoint::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class, 'user_id');
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class, 'user_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class, 'uploaded_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function moderationActions(): HasMany
    {
        return $this->hasMany(ModerationAction::class, 'moderator_id');
    }

    public function offlineSyncQueue(): HasMany
    {
        return $this->hasMany(OfflineSyncQueue::class, 'user_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'user_id');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class, 'user_id');
    }
}
