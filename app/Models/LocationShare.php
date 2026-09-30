<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocationShare extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'trip_id',
        'member_id',
        'duration_mode',
        'started_at',
        'expires_at',
        'stopped_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'stopped_at' => 'datetime',
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

    public function locationPoints(): HasMany
    {
        return $this->hasMany(LocationPoint::class, 'share_id');
    }
}
