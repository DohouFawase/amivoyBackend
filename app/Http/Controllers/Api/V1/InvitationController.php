<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvitationRequest;
use App\Http\Requests\UpdateInvitationRequest;
use App\Http\Resources\InvitationResource;
use App\Models\Circle;
use App\Models\Invitation;
use App\Models\Outing;
use App\Models\Trip;
use App\Models\TripMember;
use App\Notifications\InvitationCreatedNotification;
use App\Support\GroupActivityWriter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return InvitationResource::collection(Invitation::query()
            ->where(function ($query): void {
                $query->where('invited_by', request()->user()->getKey())
                    ->orWhere('recipient_user_id', request()->user()->getKey())
                    ->orWhere('target', request()->user()->email);
            })
            ->with(['recipient', 'outing', 'circle', 'trip'])
            ->latest('created_at')
            ->paginate(25));
    }

    public function store(StoreInvitationRequest $request): InvitationResource
    {
        $shareCode = Str::random(48);
        $attributes = $request->validated();
        $invitation = Invitation::query()->create([
            ...$attributes,
            'invited_by' => $request->user()->getKey(),
            'token_hash' => hash('sha256', $shareCode),
            'max_uses' => 1,
            'use_count' => 0,
            'status' => 'pending',
            'expires_at' => $attributes['expires_at'] ?? now()->addDays(14),
        ]);
        $appDeepLink = 'amivoy://invite/'.$shareCode;
        $inviteUrl = $appDeepLink;
        $appUrl = (string) config('app.url');
        $appHost = parse_url($appUrl, PHP_URL_HOST);
        $appScheme = parse_url($appUrl, PHP_URL_SCHEME);
        $isIpAddress = is_string($appHost) && filter_var($appHost, FILTER_VALIDATE_IP) !== false;
        $isPrivateIp = $isIpAddress && filter_var($appHost, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
        $isLocalHost = ! is_string($appHost) || in_array(strtolower($appHost), ['localhost', '127.0.0.1', '::1'], true);
        if ($appScheme === 'https' && ! $isLocalHost && ! $isPrivateIp) {
            $inviteUrl = route('invitations.open', ['code' => $shareCode]);
        }
        $invitation->setAttribute('invite_url', $appDeepLink);
        $invitation->load(['recipient', 'outing', 'circle', 'trip']);

        $emailSent = null;
        if ($invitation->channel === 'email' && $invitation->target !== null) {
            $emailSent = false;
            try {
                Notification::route('mail', $invitation->target)->notify(new InvitationCreatedNotification(
                    inviterName: $request->user()->first_name ?: $request->user()->email,
                    groupName: $invitation->trip?->name ?? $invitation->outing?->title ?? $invitation->circle?->name ?? 'votre groupe',
                    inviteUrl: $inviteUrl,
                    appDeepLink: $appDeepLink,
                    expiresAt: $invitation->expires_at->format('d/m/Y à H:i'),
                ));
                $emailSent = true;
            } catch (\Throwable $exception) {
                Log::warning('Invitation email delivery failed.', [
                    'invitation_id' => $invitation->getKey(),
                    'exception_class' => $exception::class,
                ]);
            }
        }
        $invitation->setAttribute('email_sent', $emailSent);

        return new InvitationResource($invitation);
    }

    public function show(string $id): InvitationResource
    {
        $invitation = Invitation::query()
            ->where(function ($query): void {
                $query->where('invited_by', request()->user()->getKey())
                    ->orWhere('recipient_user_id', request()->user()->getKey())
                    ->orWhere('target', request()->user()->email);
            })
            ->findOrFail($id);

        return new InvitationResource($invitation->load(['recipient', 'outing', 'circle', 'trip']));
    }

    public function update(UpdateInvitationRequest $request, string $id): InvitationResource
    {
        $invitation = Invitation::query()
            ->where('invited_by', $request->user()->getKey())
            ->findOrFail($id);
        $invitation->update($request->validated());

        return new InvitationResource($invitation->refresh());
    }

    public function respond(Request $request, string $id): InvitationResource
    {
        $data = $request->validate(['status' => ['required', 'string', 'in:accepted,declined']]);
        $user = $request->user();
        $invitation = Invitation::query()
            ->where(function ($query) use ($user): void {
                $query->where('recipient_user_id', $user->getKey())
                    ->orWhere(function ($target) use ($user): void {
                        $target->whereNull('recipient_user_id')->where('target', $user->email);
                    });
            })
            ->findOrFail($id);

        return new InvitationResource($this->applyResponse($invitation, $user, $data['status'])->load(['recipient', 'outing', 'circle', 'trip']));
    }

    public function respondByCode(Request $request): InvitationResource
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:accepted,declined'],
        ]);
        $invitation = Invitation::query()->where('token_hash', hash('sha256', $data['code']))->firstOrFail();

        return new InvitationResource($this->applyResponse($invitation, $request->user(), $data['status'])->load(['recipient', 'outing', 'circle', 'trip']));
    }

    public function destroy(string $id): Response
    {
        $invitation = Invitation::query()
            ->where('invited_by', request()->user()->getKey())
            ->findOrFail($id);
        $invitation->delete();

        return response()->noContent();
    }

    private function applyResponse(Invitation $invitation, object $user, string $status): Invitation
    {
        abort_if($invitation->status !== 'pending', 409, 'Cette invitation a déjà reçu une réponse.');
        abort_if($invitation->expires_at?->isPast(), 410, 'Cette invitation a expiré.');
        abort_if(($invitation->max_uses ?? 1) <= ($invitation->use_count ?? 0), 410, 'Le lien d’invitation a déjà été utilisé.');

        if ($invitation->target !== null && filter_var($invitation->target, FILTER_VALIDATE_EMAIL)) {
            abort_unless(mb_strtolower($invitation->target) === mb_strtolower($user->email), 403);
        }

        $displayName = $user->first_name ?: $user->email;
        $invitation->update([
            'recipient_user_id' => $user->getKey(),
            'status' => $status,
            'responded_at' => now(),
            'use_count' => ($invitation->use_count ?? 0) + 1,
        ]);

        if ($status !== 'accepted') {
            return $invitation->refresh();
        }

        if ($invitation->outing_id !== null) {
            $outing = Outing::query()->findOrFail($invitation->outing_id);
            $participants = array_values(array_unique([...($outing->participant_user_ids ?? []), (string) $user->getKey()]));
            $guests = array_values(array_unique([...($outing->guests ?? []), $displayName]));
            $attending = array_values(array_unique([...($outing->attending ?? []), $displayName]));
            $outing->update([
                'participant_user_ids' => $participants,
                'guests' => $guests,
                'attending' => $attending,
            ]);
            $this->recordEvent($outing->participant_user_ids ?? [], 'outing', (string) $outing->getKey(), $outing->title, $displayName.' a accepté l’invitation', '/outing/'.$outing->getKey(), $displayName);
        } elseif ($invitation->circle_id !== null) {
            $circle = Circle::query()->findOrFail($invitation->circle_id);
            $members = array_values(array_unique([...($circle->member_user_ids ?? []), (string) $user->getKey()]));
            $names = array_values(array_unique([...($circle->members ?? []), $displayName]));
            $circle->update(['member_user_ids' => $members, 'members' => $names]);
            $this->recordEvent($circle->member_user_ids ?? [], 'circle', (string) $circle->getKey(), $circle->name, $displayName.' a rejoint le cercle', '/circles', $displayName);
        } elseif ($invitation->trip_id !== null) {
            $trip = Trip::query()->findOrFail($invitation->trip_id);
            TripMember::query()->firstOrCreate(
                ['trip_id' => $trip->getKey(), 'user_id' => $user->getKey()],
                ['role' => 'member', 'status' => 'active', 'joined_at' => now()],
            );
            $this->recordEvent([...$trip->members()->where('status', 'active')->whereNotNull('user_id')->pluck('user_id')->map(fn ($id) => (string) $id)->all(), (string) $trip->creator_id], 'trip', (string) $trip->getKey(), $trip->name, $displayName.' a rejoint le voyage', '/trip/'.$trip->getKey(), $displayName);
        }

        return $invitation->refresh();
    }

    private function recordEvent(array $userIds, string $category, string $groupId, string $groupName, string $title, string $href, string $actor): void
    {
        GroupActivityWriter::record($userIds, [
            'category' => $category,
            'group_id' => $groupId,
            'group_name' => $groupName,
            'title' => $title,
            'description' => 'Réponse enregistrée',
            'actor' => $actor,
            'icon' => 'group',
            'href' => $href,
        ]);
    }
}
