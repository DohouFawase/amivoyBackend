<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invitation extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'trip_id', 'circle_id', 'outing_id', 'recipient_user_id', 'responded_at',
        'invited_by', 'token_hash', 'channel', 'target', 'max_uses', 'use_count',
        'status', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'max_uses' => 'integer',
            'use_count' => 'integer',
            'expires_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function circle(): BelongsTo
    {
        return $this->belongsTo(Circle::class, 'circle_id');
    }

    public function outing(): BelongsTo
    {
        return $this->belongsTo(Outing::class, 'outing_id');
    }

    public function invited_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}
