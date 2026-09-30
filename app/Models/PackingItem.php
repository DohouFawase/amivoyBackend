<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackingItem extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'trip_id',
        'added_by',
        'title',
        'category',
        'quantity',
        'is_packed',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'is_packed' => 'boolean',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
