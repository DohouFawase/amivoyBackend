<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationCreatedNotification extends Notification
{
    public function __construct(
        public readonly string $inviterName,
        public readonly string $groupName,
        public readonly string $inviteUrl,
        public readonly string $appDeepLink,
        public readonly string $expiresAt,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->from((string) config('mail.from.address'), 'Amivoy')
            ->subject('Vous êtes invité·e à rejoindre '.$this->groupName)
            ->view([
                'emails.invitation',
                'emails.invitation-text',
            ], [
                'inviterName' => $this->inviterName,
                'groupName' => $this->groupName,
                'inviteUrl' => $this->inviteUrl,
                'appDeepLink' => $this->appDeepLink,
                'expiresAt' => $this->expiresAt,
            ]);
    }
}
