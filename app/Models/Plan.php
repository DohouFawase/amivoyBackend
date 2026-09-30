<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'code',
        'max_members',
        'photo_quota_mb',
        'features',
        'price',
        'billing_period',
    ];

    protected function casts(): array
    {
        return [
            'max_members' => 'integer',
            'photo_quota_mb' => 'integer',
            'features' => 'array',
            'price' => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }
}
