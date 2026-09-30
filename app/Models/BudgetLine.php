<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetLine extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'budget_id',
        'category',
        'planned_amount',
    ];

    protected function casts(): array
    {
        return [
            'planned_amount' => 'integer',
        ];
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class, 'budget_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'budget_line_id');
    }
}
