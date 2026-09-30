<?php

namespace App\Http\Requests;

use App\Models\ExpenseParticipant;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, ExpenseParticipant::class);
    }

    public function rules(): array
    {
        return [
            'expense_id' => ['required', 'uuid', 'exists:expenses,id'],
            'member_id' => ['required', 'uuid', 'exists:trip_members,id'],
            'share_amount' => 'required|integer',
            'share_weight' => 'sometimes|nullable|numeric',
        ];
    }
}
