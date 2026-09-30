<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'country',
        'commission_rate',
        'contact_email',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:6',
        ];
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'partner_id');
    }
}
