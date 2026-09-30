<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotifyTripRequest;
use App\Models\Trip;
use App\Notifications\TripNotification;
use Illuminate\Http\JsonResponse;

class TripNotificationController extends Controller
{
    public function store(NotifyTripRequest $request, string $trip): JsonResponse
    {
        $tripModel = Trip::query()->with(['creator', 'members.user'])->findOrFail($trip);
        $recipients = $tripModel->members
            ->where('status', 'active')
            ->pluck('user')
            ->filter()
            ->keyBy(fn ($user) => (string) $user->getKey());

        if ($tripModel->creator !== null) {
            $recipients->put((string) $tripModel->creator->getKey(), $tripModel->creator);
        }

        $data = $request->validated();
        $notification = new TripNotification(
            title: $data['title'],
            body: $data['body'],
            category: $data['category'] ?? 'normal',
            tripId: (string) $tripModel->getKey(),
            data: $data['data'] ?? [],
        );

        foreach ($recipients as $recipient) {
            $recipient->notify($notification);
        }

        return response()->json([
            'message' => 'Notification mise en file pour les membres actifs du voyage.',
            'recipients' => $recipients->count(),
        ], 202);
    }
}
