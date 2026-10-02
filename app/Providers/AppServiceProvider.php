<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request): array {
            $visitorKey = $request->user()?->getAuthIdentifier()
                ? 'user:'.$request->user()->getAuthIdentifier()
                : 'ip:'.$request->ip();

            return [
                Limit::perMinute(120)->by('api:visitor:'.$visitorKey),
                Limit::perMinute(600)->by('api:ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('expensive-api', function (Request $request): array {
            $visitorKey = $request->user()?->getAuthIdentifier()
                ? 'user:'.$request->user()->getAuthIdentifier()
                : 'ip:'.$request->ip();

            return [
                Limit::perMinute(10)->by('expensive:visitor:'.$visitorKey),
                Limit::perMinute(30)->by('expensive:ip:'.$request->ip()),
            ];
        });
    }
}
