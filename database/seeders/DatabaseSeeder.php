<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $agents = collect([
            ['name' => 'Adaeze Okafor', 'email' => 'adaeze@listora.test', 'phone' => '2348035550101', 'slug' => 'adaeze-okafor', 'location' => 'Lagos', 'status' => 'verified'],
            ['name' => 'Tunde Afolabi', 'email' => 'tunde@listora.test', 'phone' => '2348065550102', 'slug' => 'tunde-afolabi', 'location' => 'Abuja', 'status' => 'verified'],
            ['name' => 'Ibiye George', 'email' => 'ibiye@listora.test', 'phone' => '2348095550103', 'slug' => 'ibiye-george', 'location' => 'Port Harcourt', 'status' => 'pending'],
        ])->map(function (array $data): Agent {
            $user = User::factory()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'role' => 'agent',
                'password' => 'password',
            ]);

            return Agent::create([
                'user_id' => $user->id,
                'public_slug' => $data['slug'],
                'display_name' => $data['name'],
                'verification_status' => $data['status'],
                'short_bio' => 'A local property professional focused on clear information, responsive service and comfortable homes.',
                'primary_location' => $data['location'],
            ]);
        });

        User::factory()->create([
            'name' => 'Chika Tenant',
            'email' => 'tenant@listora.test',
            'phone' => '2348015550104',
            'role' => 'tenant',
            'password' => 'password',
        ]);

        User::factory()->create([
            'name' => 'Musa Landlord',
            'email' => 'landlord@listora.test',
            'phone' => '2348075550105',
            'role' => 'landlord',
            'password' => 'password',
        ]);

        $locations = [
            ['state' => 'Lagos', 'city' => 'Lagos', 'area' => 'Yaba'],
            ['state' => 'FCT', 'city' => 'Abuja', 'area' => 'Gwarinpa'],
            ['state' => 'Rivers', 'city' => 'Port Harcourt', 'area' => 'Old GRA'],
            ['state' => 'Oyo', 'city' => 'Ibadan', 'area' => 'Bodija'],
            ['state' => 'Ekiti', 'city' => 'Ado-Ekiti', 'area' => 'Adebayo'],
        ];

        $types = [
            'apartment' => ['label' => 'Apartment', 'rent' => 2800000, 'beds' => 2],
            'self-contain' => ['label' => 'Self Contain', 'rent' => 650000, 'beds' => 1],
            'duplex' => ['label' => 'Duplex', 'rent' => 7500000, 'beds' => 4],
            'shared-flat' => ['label' => 'Shared Flat', 'rent' => 900000, 'beds' => 1],
            'shop' => ['label' => 'Shop', 'rent' => 1200000, 'beds' => null],
            'office' => ['label' => 'Office', 'rent' => 3500000, 'beds' => null],
        ];

        $descriptors = ['Bright', 'Quiet', 'Well-kept', 'Spacious'];
        $amenities = [
            ['water', 'Running water'],
            ['security', 'Security'],
            ['parking', 'Parking'],
            ['power', 'Backup power'],
            ['road', 'Good access road'],
        ];

        foreach (range(0, 23) as $index) {
            $typeKey = array_keys($types)[$index % count($types)];
            $type = $types[$typeKey];
            $location = $locations[$index % count($locations)];
            $descriptor = $descriptors[$index % count($descriptors)];
            $title = "{$descriptor} {$type['label']} in {$location['area']}";
            $rent = $type['rent'] + (($index % 4) * 150000);

            $property = Property::create([
                'agent_id' => $agents[$index % $agents->count()]->id,
                'title' => $title,
                'slug' => str($title)->slug().'-'.($index + 1),
                'property_type' => $typeKey,
                'listing_purpose' => 'rent',
                'state' => $location['state'],
                'city' => $location['city'],
                'area' => $location['area'],
                'display_address' => $location['area'].', '.$location['city'],
                'description' => "A practical {$type['label']} in {$location['area']} with a comfortable layout, dependable access and everyday services nearby. Confirm the property details directly with the agent before an inspection.",
                'annual_rent' => $rent,
                'bedrooms' => $type['beds'],
                'bathrooms' => $type['beds'] ? min(3, $type['beds']) : null,
                'toilets' => $type['beds'] ? min(5, $type['beds'] + 1) : 1,
                'parking_spaces' => in_array($typeKey, ['shared-flat', 'self-contain']) ? 1 : 2,
                'size_sqm' => $type['beds'] ? 38 + ($type['beds'] * 31) : 48 + (($index % 3) * 28),
                'furnishing_status' => ['unfurnished', 'semi-furnished', 'furnished'][$index % 3],
                'availability_status' => $index % 9 === 0 ? 'reserved' : 'available',
                'publication_status' => 'published',
                'featured' => $index < 8,
                'published_at' => now()->subDays($index + 1),
            ]);

            foreach ([0, 1] as $imageIndex) {
                $variant = $imageIndex + 1;
                $property->images()->create([
                    'image_path' => "/images/properties/{$typeKey}-{$variant}.webp",
                    'thumbnail_path' => "/images/properties/{$typeKey}-{$variant}-thumb.webp",
                    'alt_text' => "Property view of {$title}",
                    'sort_order' => $imageIndex,
                    'is_cover' => $imageIndex === 0,
                ]);
            }

            foreach ([$amenities[$index % 5], $amenities[($index + 1) % 5], $amenities[($index + 2) % 5]] as [$key, $label]) {
                $property->amenities()->create(['amenity_key' => $key, 'amenity_label' => $label]);
            }
        }
    }
}
