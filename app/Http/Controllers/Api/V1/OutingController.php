<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOutingRequest;
use App\Http\Requests\UpdateOutingRequest;
use App\Http\Resources\OutingResource;
use App\Http\Resources\PhotoResource;
use App\Models\Outing;
use App\Models\Photo;
use App\Models\User;
use App\Notifications\TripNotification;
use App\Support\GroupActivityWriter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class OutingController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return OutingResource::collection(
            Outing::query()->accessibleTo(request()->user())->with(['photos.uploaded_by', 'circle'])->latest('created_at')->paginate(25)
        );
    }

    public function store(StoreOutingRequest $request): OutingResource
    {
        $user = $request->user();
        $input = $request->validated();
        $memberIds = array_values(array_unique([
            (string) $user->getKey(),
            ...($input['participant_user_ids'] ?? []),
        ]));
        $guestNames = array_values(array_unique([
            $user->first_name ?: $user->email,
            ...($input['guests'] ?? []),
        ]));

        $outing = Outing::query()->create([
            ...$input,
            'creator_id' => $user->getKey(),
            'participant_user_ids' => $memberIds,
            'guests' => $guestNames,
            'attending' => [$user->first_name ?: $user->email],
            'checked_in' => [],
            'contributions' => [],
            'currency' => 'XOF',
        ]);

        $this->recordOutingEvent($outing, 'Une sortie a été organisée');

        return new OutingResource($outing->load(['photos.uploaded_by', 'circle']));
    }

    public function show(string $id): OutingResource
    {
        return new OutingResource(
            Outing::query()->accessibleTo(request()->user())->with(['photos.uploaded_by', 'circle'])->findOrFail($id)
        );
    }

    public function update(UpdateOutingRequest $request, string $id): OutingResource
    {
        $outing = Outing::query()->where('creator_id', $request->user()->getKey())->findOrFail($id);
        $before = $outing->only(['title', 'place', 'date_label', 'time_label', 'activity']);
        $outing->update($request->validated());

        if ($before !== $outing->only(['title', 'place', 'date_label', 'time_label', 'activity'])) {
            $this->recordOutingEvent($outing, 'Le programme de la sortie a changé');
            $this->notifyOutingMembers($outing, 'Le programme de la sortie a changé');
        }

        return new OutingResource($outing->refresh()->load(['photos.uploaded_by', 'circle']));
    }

    public function destroy(string $id): Response
    {
        $outing = Outing::query()->where('creator_id', request()->user()->getKey())->findOrFail($id);
        $outing->delete();

        return response()->noContent();
    }

    public function respond(Request $request, string $id): OutingResource
    {
        $request->validate(['attending' => ['required', 'boolean']]);
        $outing = Outing::query()->accessibleTo($request->user())->findOrFail($id);
        $userId = (string) $request->user()->getKey();
        $name = $request->user()->first_name ?: $request->user()->email;
        $userIds = $outing->participant_user_ids ?? [];

        abort_unless(in_array($userId, $userIds, true), 403);

        $attending = $outing->attending ?? [];
        if ($request->boolean('attending')) {
            $attending = array_values(array_unique([...$attending, $name]));
        } else {
            $attending = array_values(array_diff($attending, [$name]));
        }

        $outing->update(['attending' => $attending]);
        $this->recordOutingEvent($outing, $request->boolean('attending') ? $name.' participe à la sortie' : $name.' ne participe plus', $name);

        return new OutingResource($outing->refresh()->load(['photos.uploaded_by', 'circle']));
    }

    public function checkIn(Request $request, string $id): OutingResource
    {
        $outing = Outing::query()->accessibleTo($request->user())->findOrFail($id);
        $this->assertParticipant($outing, $request);

        $name = $request->user()->first_name ?: $request->user()->email;
        $outing->update(['checked_in' => array_values(array_unique([...($outing->checked_in ?? []), $name]))]);

        return new OutingResource($outing->refresh()->load(['photos.uploaded_by', 'circle']));
    }

    public function start(string $id): OutingResource
    {
        $outing = Outing::query()->where('creator_id', request()->user()->getKey())->findOrFail($id);
        $outing->update(['started' => true, 'ended' => false, 'started_at' => now(), 'ended_at' => null]);

        $this->recordOutingEvent($outing, 'La sortie a commencé');

        return new OutingResource($outing->refresh()->load(['photos.uploaded_by', 'circle']));
    }

    public function finish(string $id): OutingResource
    {
        $outing = Outing::query()->where('creator_id', request()->user()->getKey())->findOrFail($id);
        $outing->update(['started' => true, 'ended' => true, 'started_at' => $outing->started_at ?? now(), 'ended_at' => now()]);

        $this->recordOutingEvent($outing, 'La sortie est terminée');

        return new OutingResource($outing->refresh()->load(['photos.uploaded_by', 'circle']));
    }

    public function contribute(Request $request, string $id): OutingResource
    {
        $request->validate(['amount' => ['required', 'integer', 'min:1']]);
        $outing = Outing::query()->accessibleTo($request->user())->findOrFail($id);
        $this->assertParticipant($outing, $request);

        $name = $request->user()->first_name ?: $request->user()->email;
        $contributions = $outing->contributions ?? [];
        $contributions[] = [
            'id' => (string) str()->uuid(),
            'by' => $name,
            'amount' => (int) $request->integer('amount'),
            'created_at' => now()->toIso8601String(),
        ];
        $outing->update(['contributions' => $contributions]);

        $this->recordOutingEvent($outing, 'Une cotisation a été ajoutée', $name, $request->integer('amount').' XOF');

        return new OutingResource($outing->refresh()->load(['photos.uploaded_by', 'circle']));
    }

    public function addPhoto(Request $request, string $id): PhotoResource
    {
        $request->validate([
            'image' => ['required', 'image', 'max:20480'],
            'caption' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $outing = Outing::query()->accessibleTo($request->user())->findOrFail($id);
        $this->assertParticipant($outing, $request);
        $file = $request->file('image');
        $path = $file->store('outings/'.$outing->getKey(), 'public');

        $photo = Photo::query()->create([
            'outing_id' => $outing->getKey(),
            'uploaded_by' => $request->user()->getKey(),
            'storage_key' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'status' => 'active',
            'taken_at' => now(),
            'caption' => $request->input('caption'),
            'shared_to_story' => false,
        ]);

        $this->recordOutingEvent($outing, 'Une photo a été ajoutée au carnet', $request->user()->first_name ?: $request->user()->email, $photo->caption);

        return new PhotoResource($photo->load('uploaded_by'));
    }

    public function toggleStory(Request $request, string $id, string $photoId): PhotoResource
    {
        $outing = Outing::query()->accessibleTo($request->user())->findOrFail($id);
        $photo = $outing->photos()->findOrFail($photoId);
        $shared = ! $photo->shared_to_story;
        $photo->update(['shared_to_story' => $shared]);

        if ($shared) {
            $this->recordOutingEvent($outing, 'Un souvenir a été publié dans la Story Amivoy', $request->user()->first_name ?: $request->user()->email, $photo->caption);
        }

        return new PhotoResource($photo->refresh()->load('uploaded_by'));
    }

    private function notifyOutingMembers(Outing $outing, string $title): void
    {
        $members = User::query()
            ->whereIn('id', $outing->participant_user_ids ?? [])
            ->get()
            ->keyBy(fn (User $user) => (string) $user->getKey());

        if ($outing->creator !== null) {
            $members->put((string) $outing->creator->getKey(), $outing->creator);
        }

        $notification = new TripNotification(
            title: $title,
            body: $outing->title.' · '.$outing->date_label.' à '.$outing->time_label.' · '.$outing->place,
            category: 'normal',
            outingId: (string) $outing->getKey(),
            data: ['href' => '/outing/'.$outing->getKey()],
        );

        foreach ($members as $member) {
            $member->notify($notification);
        }
    }

    private function assertParticipant(Outing $outing, Request $request): void
    {
        abort_unless(in_array((string) $request->user()->getKey(), $outing->participant_user_ids ?? [], true), 403);
    }

    private function recordOutingEvent(Outing $outing, string $title, ?string $actor = null, ?string $description = null): void
    {
        GroupActivityWriter::record($outing->participant_user_ids ?? [(string) $outing->creator_id], [
            'category' => 'outing',
            'group_id' => (string) $outing->getKey(),
            'group_name' => $outing->title,
            'title' => $title,
            'description' => $description ?? ($outing->place.' · '.$outing->date_label.' à '.$outing->time_label),
            'actor' => $actor ?? (request()->user()->first_name ?: request()->user()->email),
            'icon' => 'outing',
            'href' => '/outing/'.$outing->getKey(),
        ]);
    }
}
