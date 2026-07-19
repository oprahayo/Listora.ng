<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Property> */
class PropertyFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['apartment', 'self-contain', 'duplex', 'shared-flat', 'shop', 'office']);
        $area = fake()->randomElement(['Yaba', 'Gwarinpa', 'GRA', 'Bodija', 'Adebayo']);
        $title = fake()->randomElement(['Bright', 'Quiet', 'Spacious', 'Modern']).' '.str($type)->headline().' in '.$area;

        return [
            'agent_id' => Agent::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'property_type' => $type,
            'listing_purpose' => 'rent',
            'state' => 'Lagos',
            'city' => 'Lagos',
            'area' => $area,
            'display_address' => $area.', Lagos',
            'description' => 'A clean, practical property with good access roads and everyday services close by.',
            'annual_rent' => fake()->numberBetween(4, 80) * 100000,
            'bedrooms' => in_array($type, ['shop', 'office']) ? null : fake()->numberBetween(1, 4),
            'bathrooms' => in_array($type, ['shop', 'office']) ? null : fake()->numberBetween(1, 3),
            'toilets' => fake()->numberBetween(1, 4),
            'parking_spaces' => fake()->numberBetween(0, 3),
            'size_sqm' => fake()->numberBetween(28, 320),
            'furnishing_status' => fake()->randomElement(['unfurnished', 'semi-furnished', 'furnished']),
            'availability_status' => 'available',
            'publication_status' => 'published',
            'featured' => false,
            'published_at' => now()->subDays(fake()->numberBetween(1, 45)),
        ];
    }

    public function draft(): static
    {
        return $this->state(['publication_status' => 'draft', 'published_at' => null]);
    }

    public function archived(): static
    {
        return $this->state(['publication_status' => 'archived']);
    }
}
