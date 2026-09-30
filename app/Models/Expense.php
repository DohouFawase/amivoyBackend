<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'trip_id',
        'paid_by',
        'budget_line_id',
        'title',
        'amount',
        'currency',
        'fx_rate',
        'split_mode',
        'spent_at',
        'place_label',
        'receipt_url',
        'comment',
        'icon',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'fx_rate' => 'decimal:6',
            'spent_at' => 'datetime',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function paid_by(): BelongsTo
    {
        return $this->belongsTo(TripMember::class, 'paid_by');
    }

    public function budget_line(): BelongsTo
    {
        return $this->belongsTo(BudgetLine::class, 'budget_line_id');
    }

    public function expenseParticipants(): HasMany
    {
        return $this->hasMany(ExpenseParticipant::class, 'expense_id');
    }
}
