<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyAmenity;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PropertyAmenity> */
class PropertyAmenityFactory extends Factory
{
    public function definition(): array
    {
        $amenity = fake()->randomElement([
            'water' => 'Running water',
            'security' => 'Security',
            'parking' => 'Parking',
            'power' => 'Backup power',
        ]);

        return [
            'property_id' => Property::factory(),
            'amenity_key' => str($amenity)->slug(),
            'amenity_label' => $amenity,
        ];
    }
}
