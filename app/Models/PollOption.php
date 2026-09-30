<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PollOption extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'poll_id',
        'label',
        'payload',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'position' => 'integer',
        ];
    }

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class, 'poll_id');
    }

    public function pollAnswers(): HasMany
    {
        return $this->hasMany(PollAnswer::class, 'option_id');
    }
}
