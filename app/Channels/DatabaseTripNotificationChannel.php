<?php

namespace App\Channels;

use App\Models\Notification as TripNotificationRecord;
use App\Notifications\TripNotification;

class DatabaseTripNotificationChannel
{
    public function send(object $notifiable, TripNotification $notification): void
    {
        TripNotificationRecord::query()->create([
            'user_id' => $notifiable->getKey(),
            'trip_id' => $notification->tripId,
            'outing_id' => $notification->outingId,
            'category' => $notification->category,
            'type' => TripNotification::class,
            'title' => $notification->title,
            'body' => $notification->body,
            'data' => $notification->data,
            'sent_at' => now(),
        ]);
    }
}
