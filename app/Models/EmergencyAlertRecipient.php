<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmergencyAlertRecipient extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'alert_id',
        'member_id',
        'delivered_at',
        'acknowledged_at',
    ];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function alert(): BelongsTo
    {
        return $this->belongsTo(EmergencyAlert::class, 'alert_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(TripMember::class, 'member_id');
    }
}
