<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'trip_id',
        'service_id',
        'booked_by',
        'activity_id',
        'quantity',
        'total_amount',
        'status',
        'partner_ref',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'total_amount' => 'integer',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function booked_by(): BelongsTo
    {
        return $this->belongsTo(TripMember::class, 'booked_by');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }
}
