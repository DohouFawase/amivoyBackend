<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class TripJournalEntryResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['body'] = $data['content'] ?? '';
        $data['day'] = $data['day_label'] ?? null;

        return $data;
    }
}
