<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationPreference extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'push_enabled',
        'email_enabled',
        'sms_enabled',
        'per_type_settings',
        'quiet_from',
        'quiet_to',
    ];

    protected function casts(): array
    {
        return [
            'push_enabled' => 'boolean',
            'email_enabled' => 'boolean',
            'sms_enabled' => 'boolean',
            'per_type_settings' => 'array',
            'quiet_from' => 'datetime',
            'quiet_to' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
