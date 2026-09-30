<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExchangeRate extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'base',
        'quote',
        'rate',
        'source',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:6',
            'fetched_at' => 'datetime',
        ];
    }
}
