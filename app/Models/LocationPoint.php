<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocationPoint extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'share_id',
        'lat',
        'lng',
        'accuracy_m',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:6',
            'lng' => 'decimal:6',
            'accuracy_m' => 'float',
            'recorded_at' => 'datetime',
        ];
    }

    public function share(): BelongsTo
    {
        return $this->belongsTo(LocationShare::class, 'share_id');
    }
}
