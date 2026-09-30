<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\ActivityAttendee;
use App\Models\Budget;
use App\Models\BudgetLine;
use App\Models\Circle;
use App\Models\EmergencyAlert;
use App\Models\EmergencyAlertRecipient;
use App\Models\ExchangeRate;
use App\Models\Expense;
use App\Models\ExpenseParticipant;
use App\Models\Invitation;
use App\Models\LocationPoint;
use App\Models\LocationShare;
use App\Models\ModerationAction;
use App\Models\Outing;
use App\Models\Partner;
use App\Models\Photo;
use App\Models\PhotoComment;
use App\Models\Place;
use App\Models\Plan;
use App\Models\Poll;
use App\Models\PollAnswer;
use App\Models\PollOption;
use App\Models\Reminder;
use App\Models\Service;
use App\Models\Trip;
use App\Models\TripMember;
use App\Models\TripPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TripV1Access
{
    /** @var array<class-string, string> */
    private const RELATED_TRIP = [
        BudgetLine::class => 'budget',
        PollOption::class => 'poll',
        PollAnswer::class => 'option.poll',
        ExpenseParticipant::class => 'expense',
        PhotoComment::class => 'photo',
        ActivityAttendee::class => 'activity',
        LocationPoint::class => 'share',
        EmergencyAlertRecipient::class => 'alert',
        TripPlace::class => 'trip',
    ];

    /** @var list<class-string> */
    private const GLOBAL_READ = [
        Place::class,
        Plan::class,
        Partner::class,
        Service::class,
        ExchangeRate::class,
    ];

    /** @param Builder<Model> $query
     * @param  class-string<Model>  $modelClass
     * @return Builder<Model>
     */
    public static function scope(Builder $query, User $user, string $modelClass): Builder
    {
        $model = new $modelClass;
        $fillable = $model->getFillable();

        if ($model instanceof Trip) {
            return $query->where(function (Builder $query) use ($user): void {
                $query->where('creator_id', $user->id)
                    ->orWhereIn('id', self::tripMemberIds($user));
            });
        }

        if ($modelClass === Photo::class) {
            return $query->where(function (Builder $query) use ($user): void {
                $query->whereIn('trip_id', self::tripMemberIds($user))
                    ->orWhereIn('outing_id', Outing::query()->accessibleTo($user)->select('id'));
            });
        }

        if ($modelClass === Invitation::class) {
            return $query->where(function (Builder $query) use ($user): void {
                $query->whereIn('trip_id', self::tripMemberIds($user))
                    ->orWhereIn('outing_id', Outing::query()->accessibleTo($user)->select('id'))
                    ->orWhereIn('circle_id', Circle::query()->accessibleTo($user)->select('id'))
                    ->orWhere('invited_by', $user->id)
                    ->orWhere('recipient_user_id', $user->id);
            });
        }

        if ($modelClass === Reminder::class) {
            return $query->where(function (Builder $query) use ($user): void {
                $query->whereIn('trip_id', self::tripMemberIds($user))
                    ->orWhereIn('outing_id', Outing::query()->accessibleTo($user)->select('id'));
            });
        }

        if (in_array('trip_id', $fillable, true)) {
            return $query->whereIn('trip_id', self::tripMemberIds($user));
        }

        if (in_array('user_id', $fillable, true)) {
            return $query->where('user_id', $user->id);
        }

        if (in_array('reporter_id', $fillable, true)) {
            return $query->where('reporter_id', $user->id);
        }

        if (in_array('member_id', $fillable, true)) {
            return $query->whereIn('member_id', self::memberIds($user));
        }

        if (in_array('from_member', $fillable, true) && in_array('to_member', $fillable, true)) {
            return $query->where(function (Builder $query) use ($user): void {
                $query->whereIn('from_member', self::memberIds($user))
                    ->orWhereIn('to_member', self::memberIds($user));
            });
        }

        if (isset(self::RELATED_TRIP[$modelClass])) {
            return $query->whereHas(self::RELATED_TRIP[$modelClass], function (Builder $query) use ($user): void {
                $query->whereIn('trip_id', self::tripMemberIds($user));
            });
        }

        if ($modelClass === ModerationAction::class && in_array($user->platform_role, ['admin', 'moderator'], true)) {
            return $query;
        }

        if (in_array($modelClass, self::GLOBAL_READ, true) && $user->platform_role !== 'admin') {
            return $query;
        }

        return $query->whereRaw('1 = 0');
    }

    public static function authorize(Request $request, string $modelClass): bool
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return false;
        }

        if ($modelClass === Trip::class) {
            $tripId = $request->route('id');

            return $tripId === null ? true : self::canManageTrip($user, $tripId);
        }

        if (in_array($modelClass, self::GLOBAL_READ, true) && $user->platform_role !== 'admin') {
            return false;
        }

        if ($modelClass === ModerationAction::class && ! in_array($user->platform_role, ['admin', 'moderator'], true)) {
            return false;
        }

        $model = new $modelClass;
        foreach (['user_id', 'creator_id', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if ($modelClass === TripMember::class && $ownerField === 'user_id') {
                continue;
            }
            if ($request->filled($ownerField) && (string) $request->input($ownerField) !== (string) $user->id) {
                return false;
            }
        }

        $id = $request->route('id');

        if ($id !== null) {
            $record = self::scope($modelClass::query(), $user, $modelClass)->find($id);

            if ($record === null) {
                return false;
            }

            if ($modelClass === TripMember::class) {
                if ((string) $record->user_id === (string) $user->id) {
                    return ! $request->hasAny(['role', 'status', 'trip_id', 'user_id']);
                }

                return self::canManageTrip($user, (string) $record->trip_id);
            }

            return ! $request->filled('trip_id') || self::canAccessTrip($user, $request->input('trip_id'));
        }

        if ($request->filled('outing_id')) {
            return Outing::query()->accessibleTo($user)->whereKey($request->input('outing_id'))->exists();
        }

        if ($request->filled('circle_id')) {
            return Circle::query()->accessibleTo($user)->whereKey($request->input('circle_id'))->exists();
        }

        if ($request->filled('trip_id')) {
            if ($modelClass === TripMember::class) {
                return self::canManageTrip($user, $request->input('trip_id'));
            }

            return self::canAccessTrip($user, $request->input('trip_id'));
        }

        $parentTripId = self::parentTripId($request, $modelClass);

        if ($parentTripId !== null) {
            return self::canAccessTrip($user, $parentTripId);
        }

        if ($modelClass === ModerationAction::class) {
            return in_array($user->platform_role, ['admin', 'moderator'], true);
        }

        if (in_array('user_id', $model->getFillable(), true)) {
            return ! $request->filled('user_id') || (string) $request->input('user_id') === (string) $user->id;
        }

        if (in_array($modelClass, self::GLOBAL_READ, true)) {
            return $user->platform_role === 'admin';
        }

        return false;
    }

    private static function parentTripId(Request $request, string $modelClass): ?string
    {
        $parents = [
            BudgetLine::class => ['budget_id', Budget::class, 'trip_id'],
            PollOption::class => ['poll_id', Poll::class, 'trip_id'],
            PollAnswer::class => ['option_id', PollOption::class, 'poll.trip_id'],
            ExpenseParticipant::class => ['expense_id', Expense::class, 'trip_id'],
            PhotoComment::class => ['photo_id', Photo::class, 'trip_id'],
            ActivityAttendee::class => ['activity_id', Activity::class, 'trip_id'],
            LocationPoint::class => ['share_id', LocationShare::class, 'trip_id'],
            EmergencyAlertRecipient::class => ['alert_id', EmergencyAlert::class, 'trip_id'],
        ];

        if (! isset($parents[$modelClass])) {
            return null;
        }

        [$field, $parentClass, $path] = $parents[$modelClass];
        $parent = $request->filled($field) ? $parentClass::query()->find($request->input($field)) : null;

        if ($parent === null) {
            return null;
        }

        foreach (explode('.', $path) as $segment) {
            $parent = $segment === 'trip_id' ? $parent->trip_id : $parent->{$segment};

            if ($parent === null) {
                return null;
            }
        }

        return (string) $parent;
    }

    public static function canManageGlobalResource(User $user, string $modelClass): bool
    {
        return ! in_array($modelClass, self::GLOBAL_READ, true) || $user->platform_role === 'admin';
    }

    public static function canManageTrip(User $user, string $tripId): bool
    {
        return Trip::query()
            ->whereKey($tripId)
            ->where(function (Builder $query) use ($user): void {
                $query->where('creator_id', $user->id)
                    ->orWhereHas('members', fn (Builder $members) => $members
                        ->where('user_id', $user->id)
                        ->whereIn('role', ['creator', 'co_organizer'])
                        ->where('status', 'active'));
            })
            ->exists();
    }

    private static function canAccessTrip(User $user, string $tripId): bool
    {
        return Trip::query()
            ->whereKey($tripId)
            ->where(function (Builder $query) use ($user): void {
                $query->where('creator_id', $user->id)
                    ->orWhereHas('members', fn (Builder $members) => $members
                        ->where('user_id', $user->id)
                        ->where('status', 'active'));
            })
            ->exists();
    }

    private static function tripMemberIds(User $user): Builder
    {
        return Trip::query()
            ->select('trips.id')
            ->where(function (Builder $query) use ($user): void {
                $query->where('creator_id', $user->id)
                    ->orWhereHas('members', fn (Builder $members) => $members
                        ->where('user_id', $user->id)
                        ->where('status', 'active'));
            });
    }

    private static function memberIds(User $user): Builder
    {
        return TripMember::query()
            ->select('id')
            ->where('status', 'active')
            ->where(function (Builder $query) use ($user): void {
                $query->where('user_id', $user->id)
                    ->orWhereHas('trip', fn (Builder $trips) => $trips->where('creator_id', $user->id));
            });
    }
}
