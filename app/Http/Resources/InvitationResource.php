<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class InvitationResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['name'] = $this->resource->target
            ?? $this->resource->recipient?->first_name
            ?? $this->resource->recipient?->email;
        $data['destination'] = $this->resource->outing?->place
            ?? $this->resource->trip?->destination_label
            ?? $this->resource->circle?->name;
        $data['channelCode'] = $this->resource->channel;
        if (array_key_exists('email_sent', $this->resource->getAttributes())) {
            $data['email_sent'] = $this->resource->getAttribute('email_sent');
        }
        $data['channelLabel'] = match ($this->resource->channel) {
            'whatsapp' => 'WhatsApp',
            'sms' => 'SMS',
            'email' => 'E-mail',
            'link' => 'Lien',
            default => $this->resource->channel,
        };
        $data['statusCode'] = $this->resource->status;
        $data['responseStatus'] = match ($this->resource->status) {
            'accepted' => 'Acceptée',
            'declined' => 'Refusée',
            'pending' => 'En attente',
            'sent' => 'Envoyée',
            default => $this->resource->status,
        };

        $data['channel'] = $data['channelLabel'];
        $data['status'] = $data['responseStatus'];

        return $data;
    }
}
