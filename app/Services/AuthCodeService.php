<?php

namespace App\Services;

use App\Models\AuthChallenge;
use App\Models\User;
use App\Notifications\AuthCodeNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthCodeService
{
    public function issue(User $user, string $purpose): void
    {
        $rateLimitKey = 'auth-code-issue:'.$user->getKey();
        abort_if(RateLimiter::tooManyAttempts($rateLimitKey, 3), 429, 'Trop de codes ont été demandés. Réessayez plus tard.');
        RateLimiter::hit($rateLimitKey, 60);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($user, $purpose, $code): void {
            AuthChallenge::query()
                ->where('user_id', $user->getKey())
                ->where('purpose', $purpose)
                ->whereNull('consumed_at')
                ->update(['consumed_at' => now()]);

            AuthChallenge::query()->create([
                'user_id' => $user->getKey(),
                'purpose' => $purpose,
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(10),
            ]);
        });

        $user->notify(new AuthCodeNotification($code, $purpose));
    }

    public function consume(string $email, string $purpose, string $code): User
    {
        $userId = User::query()->where('email', $email)->value('id');

        $user = DB::transaction(function () use ($userId, $purpose, $code): ?User {
            if ($userId === null) {
                return null;
            }

            $challenge = AuthChallenge::query()
                ->where('user_id', $userId)
                ->where('purpose', $purpose)
                ->whereNull('consumed_at')
                ->latest('created_at')
                ->lockForUpdate()
                ->first();

            if ($challenge === null || $challenge->expires_at->isPast() || $challenge->attempts >= 5) {
                return null;
            }

            if (! Hash::check($code, $challenge->code_hash)) {
                $challenge->increment('attempts');

                return null;
            }

            $challenge->update(['consumed_at' => now()]);

            return User::query()->find($userId);
        });

        if ($user === null) {
            throw ValidationException::withMessages([
                'code' => ['Le code est invalide, expiré ou a atteint sa limite d’essais.'],
            ]);
        }

        return $user;
    }
}
