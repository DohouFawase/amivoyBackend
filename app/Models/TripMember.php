<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripMember extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'trip_id',
        'user_id',
        'role',
        'status',
        'joined_at',
        'left_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pollAnswers(): HasMany
    {
        return $this->hasMany(PollAnswer::class, 'member_id');
    }

    public function destinationProposals(): HasMany
    {
        return $this->hasMany(DestinationProposal::class, 'proposed_by');
    }

    public function exclusionRequestsByTargetMember(): HasMany
    {
        return $this->hasMany(ExclusionRequest::class, 'target_member_id');
    }

    public function exclusionRequestsByRequestedBy(): HasMany
    {
        return $this->hasMany(ExclusionRequest::class, 'requested_by');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class, 'member_id');
    }

    public function tripPlaces(): HasMany
    {
        return $this->hasMany(TripPlace::class, 'added_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'responsible_id');
    }

    public function activityAttendees(): HasMany
    {
        return $this->hasMany(ActivityAttendee::class, 'member_id');
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class, 'member_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'paid_by');
    }

    public function expenseParticipants(): HasMany
    {
        return $this->hasMany(ExpenseParticipant::class, 'member_id');
    }

    public function settlementsByFromMember(): HasMany
    {
        return $this->hasMany(Settlement::class, 'from_member');
    }

    public function settlementsByToMember(): HasMany
    {
        return $this->hasMany(Settlement::class, 'to_member');
    }

    public function photoComments(): HasMany
    {
        return $this->hasMany(PhotoComment::class, 'member_id');
    }

    public function locationShares(): HasMany
    {
        return $this->hasMany(LocationShare::class, 'member_id');
    }

    public function emergencyAlerts(): HasMany
    {
        return $this->hasMany(EmergencyAlert::class, 'member_id');
    }

    public function emergencyAlertRecipients(): HasMany
    {
        return $this->hasMany(EmergencyAlertRecipient::class, 'member_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'booked_by');
    }
}
