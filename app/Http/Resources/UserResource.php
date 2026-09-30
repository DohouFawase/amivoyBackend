<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserResource extends ApiResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'firstName' => $this->first_name,
            'last_name' => $this->last_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar_url' => $this->avatar_url,
            'language' => $this->language,
            'country' => $this->country,
            'interests' => $this->interests ?? [],
            'email_verified' => (bool) $this->email_verified,
            'phone_verified' => (bool) $this->phone_verified,
            'two_factor_enabled' => (bool) $this->two_factor_enabled,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
