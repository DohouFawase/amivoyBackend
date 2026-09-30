<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripPlace extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'trip_id',
        'place_id',
        'added_by',
        'likes_count',
        'status',
        'note',
        'city',
        'country',
        'position',
        'stay_start',
        'stay_end',
        'lodging_name',
        'lodging_price',
        'lodging_currency',
    ];

    protected function casts(): array
    {
        return [
            'likes_count' => 'integer',
            'position' => 'integer',
            'lodging_price' => 'integer',
            'stay_start' => 'date',
            'stay_end' => 'date',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    public function added_by(): BelongsTo
    {
        return $this->belongsTo(TripMember::class, 'added_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'trip_place_id');
    }
}
