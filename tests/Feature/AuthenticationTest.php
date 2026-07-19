<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_email_login_succeeds_for_selected_role(): void
    {
        $user = User::factory()->create([
            'email' => 'tenant@example.test',
            'phone' => '2348012345678',
            'password' => 'correct-password',
            'role' => 'tenant',
        ]);

        $this->postJson('/auth/login', [
            'identifier' => 'TENANT@example.test',
            'password' => 'correct-password',
            'role' => 'tenant',
            'return_to' => '/properties',
        ])->assertOk()->assertJsonPath('redirect', '/properties');

        $this->assertAuthenticatedAs($user);
    }

    public function test_common_nigerian_phone_format_is_normalized_for_login(): void
    {
        $user = User::factory()->create([
            'email' => null,
            'phone' => '2348091234567',
            'password' => 'correct-password',
            'role' => 'agent',
        ]);

        $this->postJson('/auth/login', [
            'identifier' => '0809 123 4567',
            'password' => 'correct-password',
            'role' => 'agent',
        ])->assertOk();

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_role_login_is_rejected_with_generic_language(): void
    {
        User::factory()->create([
            'email' => 'agent@example.test',
            'password' => 'correct-password',
            'role' => 'agent',
        ]);

        $this->postJson('/auth/login', [
            'identifier' => 'agent@example.test',
            'password' => 'correct-password',
            'role' => 'landlord',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('identifier')
            ->assertJsonPath('errors.identifier.0', 'We could not sign you in with those details and role.');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        $identifier = 'limited@example.test';
        RateLimiter::clear(strtolower($identifier).'|127.0.0.1');

        foreach (range(1, 5) as $attempt) {
            $this->postJson('/auth/login', [
                'identifier' => $identifier,
                'password' => 'incorrect-password',
                'role' => 'tenant',
            ])->assertUnprocessable();
        }

        $this->postJson('/auth/login', [
            'identifier' => $identifier,
            'password' => 'incorrect-password',
            'role' => 'tenant',
        ])->assertTooManyRequests();
    }
}
