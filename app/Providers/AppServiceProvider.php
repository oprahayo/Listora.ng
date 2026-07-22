<?php

namespace App\Providers;

use App\Domain\Auth\Contracts\OtpDispatcher;
use App\Domain\Auth\LogOtpDispatcher;
use App\Domain\Auth\PhoneNormalizer;
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
            $input = (string) $request->input('identifier');
            $identifier = filter_var($input, FILTER_VALIDATE_EMAIL)
                ? strtolower($input)
                : (PhoneNormalizer::normalize($input) ?? strtolower($input));

            return Limit::perMinute(5)->by($identifier.'|'.$request->ip());
        });

        RateLimiter::for('otp', function (Request $request) {
            $input = (string) $request->input('identifier');
            $identifier = filter_var($input, FILTER_VALIDATE_EMAIL)
                ? strtolower($input)
                : (PhoneNormalizer::normalize($input) ?? strtolower($input));

            return Limit::perMinute(3)->by($identifier.'|'.$request->ip());
        });
    }
}
