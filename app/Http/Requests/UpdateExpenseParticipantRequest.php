<?php

namespace App\Http\Requests;

use App\Models\ExpenseParticipant;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, ExpenseParticipant::class);
    }

    public function rules(): array
    {
        return [
            'expense_id' => ['sometimes', 'nullable', 'uuid', 'exists:expenses,id'],
            'member_id' => ['sometimes', 'nullable', 'uuid', 'exists:trip_members,id'],
            'share_amount' => 'sometimes|nullable|integer',
            'share_weight' => 'sometimes|nullable|numeric',
        ];
    }
}
