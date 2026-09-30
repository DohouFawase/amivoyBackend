<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'contribution_id',
        'settlement_id',
        'provider',
        'provider_ref',
        'amount',
        'currency',
        'status',
        'failure_reason',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contribution(): BelongsTo
    {
        return $this->belongsTo(Contribution::class, 'contribution_id');
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(Settlement::class, 'settlement_id');
    }

    public function paymentEvents(): HasMany
    {
        return $this->hasMany(PaymentEvent::class, 'payment_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'payment_id');
    }
}
