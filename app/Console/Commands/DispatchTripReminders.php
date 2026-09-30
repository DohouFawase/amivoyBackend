<?php

namespace App\Console\Commands;

use App\Models\Reminder;
use App\Models\User;
use App\Notifications\TripNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DispatchTripReminders extends Command
{
    protected $signature = 'reminders:dispatch';

    protected $description = 'Envoie les rappels de voyage arrivés à échéance.';

    public function handle(): int
    {
        Reminder::query()
            ->with(['trip.creator', 'trip.members.user', 'outing.creator'])
            ->where('status', 'scheduled')
            ->where('fire_at', '<=', now())
            ->where(function ($query): void {
                $query->whereNotNull('trip_id')->orWhereNotNull('outing_id');
            })
            ->orderBy('fire_at')
            ->chunkById(100, function ($reminders): void {
                foreach ($reminders as $reminder) {
                    DB::transaction(function () use ($reminder): void {
                        $lockedReminder = Reminder::query()
                            ->whereKey($reminder->getKey())
                            ->where('status', 'scheduled')
                            ->lockForUpdate()
                            ->first();

                        if ($lockedReminder === null) {
                            return;
                        }

                        $trip = $reminder->trip;
                        $outing = $reminder->outing;
                        $members = $trip?->members
                            ->where('status', 'active')
                            ->pluck('user')
                            ->filter()
                            ->keyBy(fn ($user) => (string) $user->getKey()) ?? collect();

                        if ($trip?->creator !== null) {
                            $members->put((string) $trip->creator->getKey(), $trip->creator);
                        }

                        if ($outing !== null) {
                            $outingMembers = User::query()
                                ->whereIn('id', $outing->participant_user_ids ?? [])
                                ->get()
                                ->keyBy(fn (User $user) => (string) $user->getKey());
                            $members = $members->merge($outingMembers);

                            if ($outing->creator !== null) {
                                $members->put((string) $outing->creator->getKey(), $outing->creator);
                            }
                        }

                        $subject = $trip?->name ?? $outing?->title ?? 'votre activité';
                        $notification = new TripNotification(
                            title: 'Rappel : '.$subject,
                            body: 'Un rappel prévu pour votre activité est arrivé.',
                            category: 'important',
                            tripId: $trip === null ? null : (string) $trip->getKey(),
                            data: [
                                'reminder_id' => (string) $lockedReminder->getKey(),
                                'target_type' => $lockedReminder->target_type,
                                'target_id' => $lockedReminder->target_id,
                                'reminder_type' => $lockedReminder->type,
                            ],
                            outingId: $outing === null ? null : (string) $outing->getKey(),
                        );

                        foreach ($members as $member) {
                            $member->notify($notification);
                        }

                        $lockedReminder->update(['status' => 'sent']);
                    });
                }
            });

        return self::SUCCESS;
    }
}
