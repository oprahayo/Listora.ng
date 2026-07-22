<?php

namespace App\Providers;

use App\Domain\Auth\Contracts\OtpProvider;
use App\Domain\Auth\LogOtpProvider;
use App\Domain\Auth\PhoneNormalizer;
use App\Domain\Auth\ProductionOtpProvider;
use App\Domain\Invitations\Contracts\InvitationDelivery;
use App\Domain\Invitations\LogInvitationDelivery;
use App\Domain\Invitations\ProductionInvitationDelivery;
use App\Domain\Verification\Contracts\CacVerificationProvider;
use App\Domain\Verification\ManualCacVerificationProvider;
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
        $this->app->bind(OtpProvider::class, fn () => config('listora.otp_provider') === 'log' && app()->environment(['local', 'testing'])
            ? new LogOtpProvider
            : new ProductionOtpProvider);
        $this->app->bind(CacVerificationProvider::class, ManualCacVerificationProvider::class);
        $this->app->bind(InvitationDelivery::class, fn () => config('listora.invitation_delivery') === 'log' && app()->environment(['local', 'testing'])
            ? new LogInvitationDelivery
            : new ProductionInvitationDelivery);
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
