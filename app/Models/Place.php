<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Place extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'provider',
        'provider_place_id',
        'name',
        'category',
        'lat',
        'lng',
        'address',
        'opening_hours',
        'cached_data',
        'cached_at',
        'country',
        'region',
        'place_type',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:6',
            'lng' => 'decimal:6',
            'opening_hours' => 'array',
            'cached_data' => 'array',
            'cached_at' => 'datetime',
        ];
    }

    public function tripPlaces(): HasMany
    {
        return $this->hasMany(TripPlace::class, 'place_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'place_id');
    }
}
