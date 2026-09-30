<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeEmailRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\SendVerificationCodeRequest;
use App\Http\Requests\TwoFactorPasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UploadAvatarRequest;
use App\Http\Requests\VerifyEmailCodeRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserSession;
use App\Services\AuthCodeService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, AuthCodeService $codes): JsonResponse
    {
        $data = $request->validated();
        $user = User::query()->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password_hash' => $data['password'],
        ]);

        $codes->issue($user, 'email_verification');

        return response()->json([
            'message' => 'Un code de confirmation a été envoyé à votre adresse e-mail.',
            'email_verification_required' => true,
            'data' => new UserResource($user),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $token = $this->jwtGuard()->attempt(['email' => $data['email'], 'password' => $data['password']]);

        if (! $token) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        $user = $this->jwtGuard()->user();

        if ($user->status !== 'active' || ! $user->email_verified) {
            $this->jwtGuard()->logout(true);

            throw ValidationException::withMessages([
                'email' => ['Confirmez votre adresse e-mail avec le code OTP avant de vous connecter.'],
            ]);
        }

        if ($user->two_factor_enabled) {
            $this->jwtGuard()->logout(true);
            app(AuthCodeService::class)->issue($user, 'two_factor_login');

            return response()->json([
                'message' => 'Un code de double authentification a été envoyé.',
                'two_factor_required' => true,
            ], 202);
        }

        return $this->tokenResponse($user, $token);
    }

    public function sendVerificationCode(SendVerificationCodeRequest $request, AuthCodeService $codes): JsonResponse
    {
        $user = User::query()->where('email', $request->validated('email'))->first();

        if ($user !== null && ! $user->email_verified) {
            $codes->issue($user, 'email_verification');
        }

        return response()->json([
            'message' => 'Si ce compte existe et n’est pas encore confirmé, un code lui a été envoyé.',
        ], 202);
    }

    public function verifyEmail(VerifyEmailCodeRequest $request, AuthCodeService $codes): JsonResponse
    {
        $data = $request->validated();
        $user = $codes->consume($data['email'], 'email_verification', $data['code']);

        if ($user->status !== 'active') {
            throw ValidationException::withMessages(['email' => ['Ce compte ne peut pas être activé.']]);
        }

        $user->forceFill(['email_verified' => true])->save();

        $token = $this->jwtGuard()->login($user);

        return $this->tokenResponse($user, $token);
    }

    public function forgotPassword(ForgotPasswordRequest $request, AuthCodeService $codes): JsonResponse
    {
        $user = User::query()->where('email', $request->validated('email'))->first();

        if ($user !== null) {
            $codes->issue($user, 'password_reset');
        }

        return response()->json([
            'message' => 'Si un compte correspond à cette adresse, un code de réinitialisation a été envoyé.',
        ], 202);
    }

    public function resetPassword(ResetPasswordRequest $request, AuthCodeService $codes): Response
    {
        $data = $request->validated();
        $user = $codes->consume($data['email'], 'password_reset', $data['code']);
        $user->forceFill([
            'password_hash' => Hash::make($data['password']),
            'jwt_version' => $user->jwt_version + 1,
        ])->save();
        $user->userSessions()->whereNull('revoked_at')->update(['revoked_at' => now()]);
        $user->tokens()->delete();

        return response()->noContent();
    }

    public function changePassword(ChangePasswordRequest $request): Response
    {
        $user = $request->user();
        $user->forceFill([
            'password_hash' => Hash::make($request->validated('password')),
            'jwt_version' => $user->jwt_version + 1,
        ])->save();
        $user->userSessions()->whereNull('revoked_at')->update(['revoked_at' => now()]);
        $sanctumToken = $user->currentAccessToken();

        if ($sanctumToken !== null) {
            $user->tokens()->delete();
        } else {
            $this->jwtGuard()->logout(true);
        }

        return response()->noContent();
    }

    public function verifyTwoFactorLogin(VerifyEmailCodeRequest $request, AuthCodeService $codes): JsonResponse
    {
        $data = $request->validated();
        $user = $codes->consume($data['email'], 'two_factor_login', $data['code']);

        if ($user->status !== 'active' || ! $user->email_verified || ! $user->two_factor_enabled) {
            throw ValidationException::withMessages(['code' => ['Ce code ne peut pas être utilisé pour cette connexion.']]);
        }

        return $this->tokenResponse($user, $this->jwtGuard()->login($user));
    }

    public function enableTwoFactor(TwoFactorPasswordRequest $request, AuthCodeService $codes): JsonResponse
    {
        $codes->issue($request->user(), 'two_factor_enable');

        return response()->json(['message' => 'Un code de confirmation a été envoyé.'], 202);
    }

    public function verifyTwoFactorEnable(VerifyOtpRequest $request, AuthCodeService $codes): JsonResponse
    {
        $user = $request->user();
        $codes->consume($user->email, 'two_factor_enable', $request->validated('code'));
        $user->forceFill(['two_factor_enabled' => true])->save();

        return response()->json(['message' => 'La double authentification est activée.']);
    }

    public function disableTwoFactor(TwoFactorPasswordRequest $request): JsonResponse
    {
        $request->user()->forceFill(['two_factor_enabled' => false])->save();

        return response()->json(['message' => 'La double authentification est désactivée.']);
    }

    public function refresh(): JsonResponse
    {
        $guard = $this->jwtGuard();
        $user = $guard->user();
        $oldToken = $guard->getToken();
        $token = $guard->refresh();

        if ($user !== null && $oldToken !== null) {
            UserSession::query()
                ->where('user_id', $user->getKey())
                ->where('access_token_hash', hash('sha256', (string) $oldToken))
                ->update(['access_token_hash' => hash('sha256', $token), 'expires_at' => now()->addMinutes($guard->getTTL())]);
        }

        return response()->json($this->jwtPayload($token));
    }

    private function tokenResponse(User $user, string $token, int $status = 200): JsonResponse
    {
        UserSession::query()->create([
            'user_id' => $user->getKey(),
            'access_token_hash' => hash('sha256', $token),
            'device_name' => substr((string) request()->userAgent(), 0, 255) ?: 'Appareil inconnu',
            'ip_address' => request()->ip(),
            'expires_at' => now()->addMinutes($this->jwtGuard()->getTTL()),
        ]);

        return response()->json([
            'data' => new UserResource($user),
            ...$this->jwtPayload($token),
        ], $status);
    }

    private function jwtGuard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return $guard;
    }

    /** @return array{access_token: string, token_type: string, expires_in: int} */
    private function jwtPayload(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $this->jwtGuard()->getTTL() * 60,
        ];
    }

    public function updateProfile(UpdateProfileRequest $request): UserResource
    {
        $user = $request->user();
        $attributes = $request->validated();

        $phoneChanged = array_key_exists('phone', $attributes) && $attributes['phone'] !== $user->phone;
        $user->fill($attributes);

        if ($phoneChanged) {
            $user->phone_verified = false;
        }

        $user->save();

        return new UserResource($user->refresh());
    }

    public function changeEmail(ChangeEmailRequest $request, AuthCodeService $codes): JsonResponse
    {
        $user = $request->user();
        $user->forceFill([
            'email' => $request->validated('email'),
            'email_verified' => false,
            'jwt_version' => $user->jwt_version + 1,
        ])->save();
        $user->userSessions()->whereNull('revoked_at')->update(['revoked_at' => now()]);
        $user->tokens()->delete();
        $codes->issue($user, 'email_verification');

        return response()->json([
            'message' => 'Un code de confirmation a été envoyé à la nouvelle adresse. Confirmez-la pour vous reconnecter.',
            'email_verification_required' => true,
        ], 202);
    }

    public function uploadAvatar(UploadAvatarRequest $request): UserResource
    {
        $user = $request->user();
        /** @var FilesystemAdapter $publicDisk */
        $publicDisk = Storage::disk('public');
        $path = $request->file('avatar')->storePublicly('avatars', 'public');
        abort_if($path === false, 500, 'L’avatar n’a pas pu être enregistré.');

        $oldAvatar = $user->avatar_url;
        $user->forceFill(['avatar_url' => $publicDisk->url($path)])->save();
        $this->deleteStoredAvatar($oldAvatar);

        return new UserResource($user->refresh());
    }

    public function deleteAvatar(): UserResource
    {
        $user = request()->user();
        $oldAvatar = $user->avatar_url;
        $user->forceFill(['avatar_url' => null])->save();
        $this->deleteStoredAvatar($oldAvatar);

        return new UserResource($user->refresh());
    }

    private function deleteStoredAvatar(?string $url): void
    {
        if ($url === null) {
            return;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $basePath = parse_url($disk->url(''), PHP_URL_PATH);
        $avatarPath = parse_url($url, PHP_URL_PATH);

        if (! is_string($basePath) || ! is_string($avatarPath)) {
            return;
        }

        $prefix = rtrim($basePath, '/').'/';

        if (! str_starts_with($avatarPath, $prefix)) {
            return;
        }

        $relativePath = substr($avatarPath, strlen($prefix));

        if (str_starts_with($relativePath, 'avatars/') && ! str_contains($relativePath, '..')) {
            $disk->delete($relativePath);
        }
    }

    public function me(): UserResource
    {
        return new UserResource(request()->user());
    }

    public function logout(): Response
    {
        $user = request()->user();
        $jwtToken = $this->jwtGuard()->getToken();
        if ($jwtToken !== null) {
            UserSession::query()
                ->where('user_id', $user->getKey())
                ->where('access_token_hash', hash('sha256', (string) $jwtToken))
                ->update(['revoked_at' => now()]);
        }
        $sanctumToken = $user->currentAccessToken();

        if ($sanctumToken !== null) {
            $sanctumToken->delete();
        } else {
            $this->jwtGuard()->logout(true);
        }

        return response()->noContent();
    }
}
