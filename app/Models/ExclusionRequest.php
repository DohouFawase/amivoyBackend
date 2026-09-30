<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExclusionRequest extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'trip_id',
        'target_member_id',
        'requested_by',
        'poll_id',
        'reason',
        'status',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'decided_at' => 'datetime',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function target_member(): BelongsTo
    {
        return $this->belongsTo(TripMember::class, 'target_member_id');
    }

    public function requested_by(): BelongsTo
    {
        return $this->belongsTo(TripMember::class, 'requested_by');
    }

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class, 'poll_id');
    }
}
