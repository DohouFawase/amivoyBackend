<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class CircleResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['createdAt'] = $data['created_at'] ?? null;
        $data['memberUserIds'] = $data['member_user_ids'] ?? [];

        return $data;
    }
}
