<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'partner_id',
        'place_id',
        'title',
        'price',
        'currency',
        'availability',
        'category',
        'description',
        'icon',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'rating' => 'integer',
            'availability' => 'array',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'service_id');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class, 'service_id');
    }
}
