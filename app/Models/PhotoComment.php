<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhotoComment extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'photo_id',
        'member_id',
        'content',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class, 'photo_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(TripMember::class, 'member_id');
    }
}
