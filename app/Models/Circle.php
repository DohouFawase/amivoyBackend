<?php

namespace App\Models;

use Database\Factories\CircleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Circle extends Model
{
    /** @use HasFactory<CircleFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = ['creator_id', 'name', 'members', 'member_user_ids'];

    protected function casts(): array
    {
        return ['members' => 'array', 'member_user_ids' => 'array'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'circle_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'circle_id');
    }

    public function outings(): HasMany
    {
        return $this->hasMany(Outing::class, 'circle_id');
    }

    public function scopeAccessibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user): void {
            $query->where('creator_id', $user->getKey())
                ->orWhereJsonContains('member_user_ids', (string) $user->getKey());
        });
    }
}
