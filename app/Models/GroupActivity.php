<?php

namespace App\Models;

use Database\Factories\GroupActivityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupActivity extends Model
{
    /** @use HasFactory<GroupActivityFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id', 'category', 'group_id', 'group_name', 'title', 'description',
        'actor', 'time_label', 'icon', 'href',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->getKey());
    }
}
