<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reminder extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'trip_id',
        'outing_id',
        'type',
        'target_type',
        'target_id',
        'fire_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'fire_at' => 'datetime',
        ];
    }

    public function outing(): BelongsTo
    {
        return $this->belongsTo(Outing::class, 'outing_id');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }
}
