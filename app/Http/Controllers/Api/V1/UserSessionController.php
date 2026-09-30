<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserSessionRequest;
use App\Http\Requests\UpdateUserSessionRequest;
use App\Http\Resources\UserSessionResource;
use App\Models\UserSession;
use App\Support\TripV1Access;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class UserSessionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return UserSessionResource::collection(TripV1Access::scope(UserSession::query(), request()->user(), UserSession::class)->latest('created_at')->paginate(25));
    }

    public function current(): AnonymousResourceCollection
    {
        return UserSessionResource::collection(request()->user()->userSessions()
            ->whereNull('revoked_at')
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest('created_at')
            ->paginate(25));
    }

    public function revokeOthers(): JsonResponse
    {
        $user = request()->user();
        $token = auth('api')->getToken();
        abort_if($token === null, 401);
        $currentHash = hash('sha256', (string) $token);
        $count = $user->userSessions()
            ->whereNull('revoked_at')
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->whereNotNull('access_token_hash')
            ->where('access_token_hash', '!=', $currentHash)
            ->update(['revoked_at' => now()]);

        return response()->json(['revoked_sessions' => $count]);
    }

    public function store(StoreUserSessionRequest $request): UserSessionResource
    {
        $attributes = $request->validated();

        foreach (['user_id', 'creator_id', 'created_by', 'reporter_id', 'uploaded_by', 'invited_by', 'actor_id'] as $ownerField) {
            if (in_array($ownerField, (new UserSession)->getFillable(), true)) {
                $attributes[$ownerField] = $request->user()->id;
            }
        }

        return new UserSessionResource(UserSession::create($attributes));
    }

    public function show(string $id): UserSessionResource
    {
        $model = TripV1Access::scope(UserSession::query(), request()->user(), UserSession::class)->findOrFail($id);

        return new UserSessionResource($model);
    }

    public function update(UpdateUserSessionRequest $request, string $id): UserSessionResource
    {
        $model = TripV1Access::scope(UserSession::query(), request()->user(), UserSession::class)->findOrFail($id);
        $model->update($request->validated());

        return new UserSessionResource($model->refresh());
    }

    public function destroy(string $id): Response
    {
        $model = TripV1Access::scope(UserSession::query(), request()->user(), UserSession::class)->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
