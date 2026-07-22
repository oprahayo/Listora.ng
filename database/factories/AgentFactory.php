<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Agent> */
class AgentFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'user_id' => User::factory()->state(['primary_role' => 'agent']),
            'public_slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'display_name' => $name,
            'verification_status' => 'verified',
            'short_bio' => 'Helping renters find straightforward, well-presented homes in Nigeria.',
            'primary_location' => fake()->randomElement(['Lagos', 'Abuja', 'Port Harcourt']),
        ];
    }
}
