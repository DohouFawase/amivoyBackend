<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ChangeEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->getKey()), function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === $this->user()->email) {
                    $fail('Saisissez une nouvelle adresse e-mail.');
                }
            }],
            'current_password' => ['required', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! Hash::check($value, $this->user()->password_hash)) {
                    $fail('Le mot de passe actuel est incorrect.');
                }
            }],
        ];
    }
}
