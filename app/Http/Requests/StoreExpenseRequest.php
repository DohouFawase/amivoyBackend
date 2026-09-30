<?php

namespace App\Http\Requests;

use App\Models\Expense;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
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
            'trip_id' => ['required', 'uuid', 'exists:trips,id'],
            'paid_by' => ['required', 'uuid', 'exists:trip_members,id'],
            'budget_line_id' => ['sometimes', 'nullable', 'uuid', 'exists:budget_lines,id'],
            'title' => 'required|string',
            'amount' => 'required|integer',
            'currency' => 'required|string',
            'fx_rate' => 'sometimes|nullable|numeric',
            'split_mode' => 'required|string',
            'spent_at' => 'sometimes|nullable|date',
            'place_label' => 'sometimes|nullable|string',
            'receipt_url' => 'sometimes|nullable|string',
            'comment' => 'sometimes|nullable|string',
            'icon' => 'sometimes|nullable|string|max:60',
            'color' => ['sometimes', 'nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}([0-9A-Fa-f]{2})?$/'],
        ];
    }
}
