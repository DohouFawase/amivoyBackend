<?php

use App\Models\Invitation;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/invite/{code}', function (string $code) {
    $invitation = Invitation::query()
        ->where('token_hash', hash('sha256', $code))
        ->with(['trip', 'circle', 'outing', 'invited_by'])
        ->firstOrFail();

    return view('invitation-link', [
        'invitation' => $invitation,
        'appDeepLink' => 'amivoy://invite/'.$code,
        'groupName' => $invitation->trip?->name ?? $invitation->outing?->title ?? $invitation->circle?->name ?? 'votre groupe',
        'inviterName' => $invitation->invited_by?->first_name ?? 'Un proche',
    ]);
})->whereAlphaNumeric('code')->name('invitations.open');
