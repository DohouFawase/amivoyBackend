<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poll extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'trip_id',
        'created_by',
        'type',
        'title',
        'multiple_choice',
        'anonymous',
        'quorum_percent',
        'status',
        'closes_at',
        'winning_option_id',
    ];

    protected function casts(): array
    {
        return [
            'multiple_choice' => 'boolean',
            'anonymous' => 'boolean',
            'quorum_percent' => 'integer',
            'closes_at' => 'datetime',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pollOptions(): HasMany
    {
        return $this->hasMany(PollOption::class, 'poll_id');
    }

    public function exclusionRequests(): HasMany
    {
        return $this->hasMany(ExclusionRequest::class, 'poll_id');
    }
}
