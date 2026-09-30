<?php

namespace App\Models;

use Database\Factories\OutingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Outing extends Model
{
    /** @use HasFactory<OutingFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'creator_id', 'circle_id', 'title', 'place', 'location_type', 'category',
        'date_label', 'time_label', 'note', 'activity', 'budget_target', 'currency',
        'contributions', 'latitude', 'longitude', 'guests', 'participant_user_ids',
        'attending', 'checked_in', 'started', 'ended', 'started_at', 'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'budget_target' => 'integer',
            'contributions' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'guests' => 'array',
            'participant_user_ids' => 'array',
            'attending' => 'array',
            'checked_in' => 'array',
            'started' => 'boolean',
            'ended' => 'boolean',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function circle(): BelongsTo
    {
        return $this->belongsTo(Circle::class, 'circle_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'outing_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class, 'outing_id');
    }

    public function scopeAccessibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user): void {
            $query->where('creator_id', $user->getKey())
                ->orWhereJsonContains('participant_user_ids', (string) $user->getKey());
        });
    }
}
