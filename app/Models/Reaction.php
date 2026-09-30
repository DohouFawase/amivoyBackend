<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reaction extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'member_id',
        'target_type',
        'target_id',
        'emoji',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(TripMember::class, 'member_id');
    }
}
