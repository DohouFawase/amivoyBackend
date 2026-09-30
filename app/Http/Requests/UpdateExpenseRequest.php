<?php

namespace App\Http\Requests;

use App\Models\Expense;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->exists('name') && ! $this->exists('title')) {
            $this->merge(['title' => $this->input('name')]);
        }
    }

    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Expense::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['sometimes', 'nullable', 'uuid', 'exists:trips,id'],
            'paid_by' => ['sometimes', 'nullable', 'uuid', 'exists:trip_members,id'],
            'budget_line_id' => ['sometimes', 'nullable', 'uuid', 'exists:budget_lines,id'],
            'title' => 'sometimes|nullable|string',
            'amount' => 'sometimes|nullable|integer',
            'currency' => 'sometimes|nullable|string',
            'fx_rate' => 'sometimes|nullable|numeric',
            'split_mode' => 'sometimes|nullable|string',
            'spent_at' => 'sometimes|nullable|date',
            'place_label' => 'sometimes|nullable|string',
            'receipt_url' => 'sometimes|nullable|string',
            'comment' => 'sometimes|nullable|string',
            'icon' => 'sometimes|nullable|string|max:60',
            'color' => ['sometimes', 'nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}([0-9A-Fa-f]{2})?$/'],
        ];
    }
}
