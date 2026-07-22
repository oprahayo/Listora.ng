<?php

namespace App\Domain\Auth;

use App\Domain\Auth\Contracts\OtpProvider;
use App\Models\OtpChallenge;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OtpService
{
    public function __construct(private readonly OtpProvider $provider) {}

    public function issue(string $identifier, string $purpose, ?User $user = null): OtpChallenge
    {
        $identifier = $this->normalizeIdentifier($identifier);
        $recent = OtpChallenge::query()->where('identifier', $identifier)->where('purpose', $purpose);

        if ((clone $recent)->where('created_at', '>', now()->subMinute())->exists()) {
            throw ValidationException::withMessages(['identifier' => 'Please wait one minute before requesting another code.']);
        }

        if ((clone $recent)->where('created_at', '>', now()->subHour())->count() >= 5) {
            throw ValidationException::withMessages(['identifier' => 'Too many code requests. Please try again later.']);
        }

        $code = (string) random_int(100000, 999999);
        $challenge = OtpChallenge::query()->create([
            'user_id' => $user?->id,
            'identifier' => $identifier,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(5),
            'requested_ip' => request()?->ip(),
        ]);

        $this->provider->send($identifier, $code, $purpose);

        return $challenge;
    }

    public function verify(string $identifier, string $purpose, string $code): OtpChallenge
    {
        $identifier = $this->normalizeIdentifier($identifier);
        $challenge = OtpChallenge::query()
            ->where('identifier', $identifier)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if (! $challenge || $challenge->expires_at->isPast()) {
            throw ValidationException::withMessages(['code' => 'This code has expired. Request a new one.']);
        }

        if ($challenge->attempts >= 5) {
            throw ValidationException::withMessages(['code' => 'Too many incorrect attempts. Request a new code.']);
        }

        if (! Hash::check($code, $challenge->code_hash)) {
            $challenge->increment('attempts');
            throw ValidationException::withMessages(['code' => 'That code is not correct. Please try again.']);
        }

        $challenge->forceFill(['consumed_at' => now()])->save();

        return $challenge;
    }

    private function normalizeIdentifier(string $identifier): string
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return str($identifier)->trim()->lower()->toString();
        }

        return PhoneNormalizer::normalize($identifier)
            ?? throw ValidationException::withMessages(['identifier' => 'Enter a valid Nigerian phone number.']);
    }
}
