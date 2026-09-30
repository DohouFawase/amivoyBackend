<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class GroupActivityResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['groupId'] = $data['group_id'] ?? null;
        $data['groupName'] = $data['group_name'] ?? null;
        $data['time'] = $data['time_label'] ?? $data['created_at'] ?? null;

        return $data;
    }
}
