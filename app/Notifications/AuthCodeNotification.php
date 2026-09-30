<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuthCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $code,
        public readonly string $purpose,
    ) {
        $this->afterCommit();
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $verification = $this->purpose === 'email_verification';
        $twoFactor = str_starts_with($this->purpose, 'two_factor');
        $subject = match (true) {
            $verification => 'Confirmez votre adresse e-mail',
            $twoFactor => 'Votre code de double authentification',
            default => 'Code de réinitialisation du mot de passe',
        };
        $instruction = match (true) {
            $verification => 'Utilisez ce code pour confirmer votre adresse e-mail.',
            $twoFactor => 'Utilisez ce code pour confirmer votre identité.',
            default => 'Utilisez ce code pour réinitialiser votre mot de passe.',
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Bonjour '.$notifiable->first_name)
            ->line($instruction)
            ->line($this->code)
            ->line('Ce code expire dans 10 minutes. Si vous n’êtes pas à l’origine de cette demande, ignorez cet e-mail.');
    }
}
