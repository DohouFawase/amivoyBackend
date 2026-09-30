<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class NotificationController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $notifications = Notification::query()
            ->where('user_id', request()->user()->getKey())
            ->latest('created_at')
            ->paginate(25);

        return NotificationResource::collection($notifications);
    }

    public function show(string $id): NotificationResource
    {
        return new NotificationResource($this->notificationForCurrentUser($id));
    }

    public function markRead(string $id): NotificationResource
    {
        $notification = $this->notificationForCurrentUser($id);

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return new NotificationResource($notification->refresh());
    }

    public function markAllRead(): Response
    {
        Notification::query()
            ->where('user_id', request()->user()->getKey())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->noContent();
    }

    public function destroy(string $id): Response
    {
        $this->notificationForCurrentUser($id)->delete();

        return response()->noContent();
    }

    private function notificationForCurrentUser(string $id): Notification
    {
        return Notification::query()
            ->where('user_id', request()->user()->getKey())
            ->findOrFail($id);
    }
}
