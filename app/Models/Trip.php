<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'creator_id',
        'circle_id',
        'name',
        'description',
        'cover_url',
        'cover_color',
        'next_label',
        'member_names',
        'display_dates',
        'duration_days',
        'destination_label',
        'start_date',
        'end_date',
        'currency',
        'planned_budget',
        'estimated_members',
        'status',
        'governance_rules',
        'visibility',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'planned_budget' => 'integer',
            'estimated_members' => 'integer',
            'governance_rules' => 'array',
            'member_names' => 'array',
            'duration_days' => 'integer',
        ];
    }

    public function circle(): BelongsTo
    {
        return $this->belongsTo(Circle::class, 'circle_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function groupActivities(): HasMany
    {
        return $this->hasMany(GroupActivity::class, 'group_id');
    }

    public function tripMembers(): HasMany
    {
        return $this->hasMany(TripMember::class, 'trip_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'trip_id');
    }

    public function polls(): HasMany
    {
        return $this->hasMany(Poll::class, 'trip_id');
    }

    public function destinationProposals(): HasMany
    {
        return $this->hasMany(DestinationProposal::class, 'trip_id');
    }

    public function exclusionRequests(): HasMany
    {
        return $this->hasMany(ExclusionRequest::class, 'trip_id');
    }

    public function tripPlaces(): HasMany
    {
        return $this->hasMany(TripPlace::class, 'trip_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'trip_id');
    }

    public function meetingPoints(): HasMany
    {
        return $this->hasMany(MeetingPoint::class, 'trip_id');
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class, 'trip_id');
    }

    public function packingItems(): HasMany
    {
        return $this->hasMany(PackingItem::class, 'trip_id');
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class, 'trip_id');
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class, 'trip_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'trip_id');
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class, 'trip_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'trip_id');
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(TripJournalEntry::class, 'trip_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class, 'trip_id');
    }

    public function locationShares(): HasMany
    {
        return $this->hasMany(LocationShare::class, 'trip_id');
    }

    public function emergencyAlerts(): HasMany
    {
        return $this->hasMany(EmergencyAlert::class, 'trip_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'trip_id');
    }

    public function offlineSyncQueue(): HasMany
    {
        return $this->hasMany(OfflineSyncQueue::class, 'trip_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'trip_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'trip_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(TripMember::class);
    }
}
