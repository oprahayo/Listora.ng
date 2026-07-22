<?php

namespace Database\Factories;

use App\Models\AgentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<AgentProfile> */
class AgentProfileFactory extends Factory
{
    protected $model = AgentProfile::class;

    public function definition(): array
    {
        $name = fake()->name();

        return [
            'user_id' => User::factory()->state(['primary_role' => 'agent']),
            'public_slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'display_name' => $name,
            'account_type' => 'individual',
            'operation_type' => 'individual_agent',
            'verification_status' => 'verified',
            'verified_at' => now(),
            'short_bio' => 'Helping renters find straightforward, well-presented homes in Nigeria.',
            'operating_state' => 'Lagos',
            'operating_city' => fake()->randomElement(['Lagos', 'Abuja', 'Port Harcourt']),
        ];
    }
}
