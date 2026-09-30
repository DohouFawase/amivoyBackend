<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $url = $this->storage_key ? Storage::disk('public')->url($this->storage_key) : null;
        $data['url'] = $url;
        $data['uri'] = $url;
        $data['createdAt'] = $data['created_at'] ?? null;
        $data['by'] = $this->resource->relationLoaded('uploaded_by')
            ? ($this->resource->uploaded_by?->first_name ?? 'Un membre')
            : null;
        $data['sharedToStory'] = $data['shared_to_story'] ?? false;

        return $data;
    }
}
