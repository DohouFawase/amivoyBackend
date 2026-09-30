<?php

namespace App\Http\Requests;

use App\Models\BudgetLine;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, BudgetLine::class);
    }

    public function rules(): array
    {
        return [
            'budget_id' => ['required', 'uuid', 'exists:budgets,id'],
            'category' => 'required|string',
            'planned_amount' => 'required|integer',
        ];
    }
}
