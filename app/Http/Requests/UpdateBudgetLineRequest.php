<?php

namespace App\Http\Requests;

use App\Models\BudgetLine;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBudgetLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, BudgetLine::class);
    }

    public function rules(): array
    {
        return [
            'budget_id' => ['sometimes', 'nullable', 'uuid', 'exists:budgets,id'],
            'category' => 'sometimes|nullable|string',
            'planned_amount' => 'sometimes|nullable|integer',
        ];
    }
}
