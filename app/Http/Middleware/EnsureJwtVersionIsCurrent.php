<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;
use Symfony\Component\HttpFoundation\Response;

class EnsureJwtVersionIsCurrent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        abort_unless($user->status === 'active', 403, 'Ce compte est désactivé.');

        if ($user->currentAccessToken() !== null) {
            return $next($request);
        }

        /** @var JWTGuard $guard */
        $guard = auth('api');
        $version = $guard->payload()->get('ver');

        abort_unless(
            is_numeric($version) && (int) $version === (int) $user->jwt_version,
            401,
            'Ce jeton a été révoqué.',
        );

        $token = $guard->getToken();
        if ($token !== null) {
            $session = UserSession::query()
                ->where('user_id', $user->getKey())
                ->where('access_token_hash', hash('sha256', (string) $token))
                ->first();
            abort_if($session?->revoked_at !== null, 401, 'Cette session a été fermée.');
        }

        return $next($request);
    }
}
