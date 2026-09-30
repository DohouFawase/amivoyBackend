<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Photo extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'trip_id',
        'outing_id',
        'caption',
        'shared_to_story',
        'uploaded_by',
        'storage_key',
        'thumbnail_key',
        'mime_type',
        'size_bytes',
        'width',
        'height',
        'status',
        'taken_at',
    ];

    protected function casts(): array
    {
        return [
            'shared_to_story' => 'boolean',
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'taken_at' => 'datetime',
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

    public function uploaded_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function photoComments(): HasMany
    {
        return $this->hasMany(PhotoComment::class, 'photo_id');
    }
}
