<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmergencyAlert extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'trip_id',
        'member_id',
        'lat',
        'lng',
        'position_is_last_known',
        'status',
        'triggered_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:6',
            'lng' => 'decimal:6',
            'position_is_last_known' => 'boolean',
            'triggered_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(TripMember::class, 'member_id');
    }

    public function emergencyAlertRecipients(): HasMany
    {
        return $this->hasMany(EmergencyAlertRecipient::class, 'alert_id');
    }
}
