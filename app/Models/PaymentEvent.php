<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentEvent extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'payment_id',
        'event_type',
        'raw_payload',
        'signature_valid',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'raw_payload' => 'array',
            'signature_valid' => 'boolean',
            'received_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}
