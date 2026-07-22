<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PropertyImage> */
class PropertyImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'image_path' => '/images/properties/apartment-1.webp',
            'thumbnail_path' => '/images/properties/apartment-1-thumb.webp',
            'alt_text' => 'Property exterior',
            'sort_order' => 0,
            'is_cover' => true,
        ];
    }
}
