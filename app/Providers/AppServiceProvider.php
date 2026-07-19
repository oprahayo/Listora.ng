<?php

namespace App\Providers;

use App\Domain\Auth\Contracts\OtpDispatcher;
use App\Domain\Auth\LogOtpDispatcher;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OtpDispatcher::class, LogOtpDispatcher::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        RateLimiter::for('login', function (Request $request) {
            $identifier = strtolower((string) $request->input('identifier'));

            return Limit::perMinute(5)->by($identifier.'|'.$request->ip());
        });

        RateLimiter::for('otp', function (Request $request) {
            $identifier = strtolower((string) $request->input('identifier'));

            return Limit::perMinute(3)->by($identifier.'|'.$request->ip());
        });
    }
}
