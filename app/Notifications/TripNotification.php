<?php

namespace App\Notifications;

use App\Channels\DatabaseTripNotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class TripNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public string $title,
        public string $body,
        public string $category = 'normal',
        public ?string $tripId = null,
        public array $data = [],
        public ?string $outingId = null,
    ) {
        $this->afterCommit();
    }

    /** @return array<int, class-string|string> */
    public function via(object $notifiable): array
    {
        $channels = [DatabaseTripNotificationChannel::class];
        $preferences = $notifiable->notificationPreferences()->latest('created_at')->first();

        if ($notifiable->email && $preferences?->email_enabled !== false) {
            $channels[] = 'mail';
        }

        if ($preferences?->push_enabled !== false && $notifiable->routeNotificationForFcm() !== []) {
            $channels[] = FcmChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->greeting('Bonjour '.$notifiable->first_name)
            ->line($this->body);
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        $data = [
            'category' => $this->category,
            'trip_id' => $this->tripId ?? '',
            'outing_id' => $this->outingId ?? '',
        ];

        foreach ($this->data as $key => $value) {
            $data[$key] = is_scalar($value) ? (string) $value : json_encode($value, JSON_THROW_ON_ERROR);
        }

        return (new FcmMessage(
            notification: new FcmNotification(title: $this->title, body: $this->body),
        ))->data($data);
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'category' => $this->category,
            'trip_id' => $this->tripId,
            'outing_id' => $this->outingId,
            'data' => $this->data,
        ];
    }
}
