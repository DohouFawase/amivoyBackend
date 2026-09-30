<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DestinationProposal extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'trip_id',
        'proposed_by',
        'name',
        'lat',
        'lng',
        'pitch',
        'estimated_cost',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:6',
            'lng' => 'decimal:6',
            'estimated_cost' => 'integer',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function proposed_by(): BelongsTo
    {
        return $this->belongsTo(TripMember::class, 'proposed_by');
    }
}
