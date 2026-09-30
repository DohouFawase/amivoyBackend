<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseParticipant extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'expense_id',
        'member_id',
        'share_amount',
        'share_weight',
    ];

    protected function casts(): array
    {
        return [
            'share_amount' => 'integer',
            'share_weight' => 'decimal:6',
        ];
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(TripMember::class, 'member_id');
    }
}
