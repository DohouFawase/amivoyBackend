<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'trip_id',
        'trip_place_id',
        'responsible_id',
        'title',
        'description',
        'starts_at',
        'duration_min',
        'estimated_cost',
        'status',
        'notes',
        'category',
        'icon',
        'location_label',
        'time_label',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'duration_min' => 'integer',
            'estimated_cost' => 'integer',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function trip_place(): BelongsTo
    {
        return $this->belongsTo(TripPlace::class, 'trip_place_id');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(TripMember::class, 'responsible_id');
    }

    public function activityAttendees(): HasMany
    {
        return $this->hasMany(ActivityAttendee::class, 'activity_id');
    }

    public function meetingPoints(): HasMany
    {
        return $this->hasMany(MeetingPoint::class, 'activity_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'activity_id');
    }
}
