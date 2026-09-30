<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contribution extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'trip_id',
        'member_id',
        'expected_amount',
        'paid_amount',
        'status',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'expected_amount' => 'integer',
            'paid_amount' => 'integer',
            'due_date' => 'date',
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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'contribution_id');
    }
}
