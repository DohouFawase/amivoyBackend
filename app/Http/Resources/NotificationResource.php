<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NotificationResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['message'] = $data['body'] ?? '';
        $data['read'] = ! empty($data['read_at']);
        $data['time'] = isset($data['created_at'])
            ? Carbon::parse($data['created_at'])->locale('fr')->diffForHumans()
            : null;

        return $data;
    }
}
