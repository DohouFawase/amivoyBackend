<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Settlement extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'trip_id',
        'from_member',
        'to_member',
        'amount',
        'status',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'confirmed_at' => 'datetime',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function from_member(): BelongsTo
    {
        return $this->belongsTo(TripMember::class, 'from_member');
    }

    public function to_member(): BelongsTo
    {
        return $this->belongsTo(TripMember::class, 'to_member');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'settlement_id');
    }
}
