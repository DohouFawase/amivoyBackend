<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PollAnswer extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'option_id',
        'member_id',
        'voted_at',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'voted_at' => 'datetime',
            'changed_at' => 'datetime',
        ];
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(PollOption::class, 'option_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(TripMember::class, 'member_id');
    }
}
