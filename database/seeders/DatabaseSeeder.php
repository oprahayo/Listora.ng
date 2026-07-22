<?php

namespace Database\Seeders;

use App\Models\AgentProfile;
use App\Models\Invitation;
use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\TenantProfile;
use App\Models\User;
use App\Models\VerificationDocument;
use App\Models\VerificationRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $demoPassword = app()->environment('production') ? Str::random(48) : 'password';
        $userFor = function (array $attributes, string $role) use ($demoPassword): User {
            $user = User::query()->where('email', $attributes['email'])->orWhere('phone', $attributes['phone'])->first() ?: new User;
            $user->forceFill([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'phone' => $attributes['phone'],
                'primary_role' => $role,
                'status' => 'active',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'password' => $demoPassword,
            ])->save();
            $user->assignRole($role);

            return $user;
        };
        $agents = collect([
            ['name' => 'Adaeze Okafor', 'email' => 'adaeze@listora.test', 'phone' => '2348035550101', 'slug' => 'adaeze-okafor', 'location' => 'Lagos', 'status' => 'verified'],
            ['name' => 'Tunde Afolabi', 'email' => 'tunde@listora.test', 'phone' => '2348065550102', 'slug' => 'tunde-afolabi', 'location' => 'Abuja', 'status' => 'verified'],
            ['name' => 'Pending Agent', 'email' => 'pending.agent@listora.test', 'phone' => '2348095550103', 'slug' => 'pending-agent', 'location' => 'Port Harcourt', 'status' => 'pending'],
        ])->map(function (array $data) use ($userFor): AgentProfile {
            $user = $userFor([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
            ], 'agent');

            return AgentProfile::query()->updateOrCreate(['user_id' => $user->id], [
                'public_slug' => $data['slug'],
                'display_name' => $data['name'],
                'account_type' => 'individual',
                'operation_type' => 'individual_agent',
                'verification_status' => $data['status'],
                'verified_at' => $data['status'] === 'verified' ? now() : null,
                'short_bio' => 'A local property professional focused on clear information, responsive service and comfortable homes.',
                'operating_state' => $data['location'] === 'Abuja' ? 'FCT' : ($data['location'] === 'Port Harcourt' ? 'Rivers' : 'Lagos'),
                'operating_city' => $data['location'],
            ]);
        });

        $tenant = $userFor([
            'name' => 'Chika Tenant',
            'email' => 'tenant@listora.test',
            'phone' => '2348015550104',
        ], 'tenant');
        TenantProfile::query()->updateOrCreate(['user_id' => $tenant->id], ['preferred_name' => 'Chika', 'preferred_contact_method' => 'app']);

        $landlord = $userFor([
            'name' => 'Musa Landlord',
            'email' => 'landlord@listora.test',
            'phone' => '2348075550105',
        ], 'landlord');
        LandlordProfile::query()->updateOrCreate(['user_id' => $landlord->id], ['preferred_name' => 'Musa', 'preferred_contact_method' => 'whatsapp']);

        $userFor([
            'name' => 'Listora Administrator',
            'email' => 'admin@listora.test',
            'phone' => '2348085550106',
        ], 'admin');

        $multi = $userFor([
            'name' => 'Multi Workspace User',
            'email' => 'multi@listora.test',
            'phone' => '2348055550107',
        ], 'agent');
        $multi->assignRole('landlord');
        $multi->assignRole('tenant');
        $multi->forceFill(['last_active_role' => null])->save();
        AgentProfile::query()->updateOrCreate(['user_id' => $multi->id], [
            'display_name' => 'Multi Workspace Properties',
            'public_slug' => 'multi-workspace-properties',
            'account_type' => 'individual',
            'operation_type' => 'individual_agent',
            'operating_state' => 'Lagos',
            'operating_city' => 'Lagos',
            'verification_status' => 'pending',
        ]);
        LandlordProfile::query()->updateOrCreate(['user_id' => $multi->id], ['preferred_name' => 'Multi', 'preferred_contact_method' => 'app']);
        TenantProfile::query()->updateOrCreate(['user_id' => $multi->id], ['preferred_name' => 'Multi', 'preferred_contact_method' => 'app']);

        $pendingProfile = $agents->firstWhere('verification_status', 'pending');
        $pendingVerification = VerificationRequest::query()->updateOrCreate(
            ['user_id' => $pendingProfile->user_id, 'verification_type' => 'individual_agent'],
            [
                'status' => 'submitted',
                'current_step' => 4,
                'identity_data' => ['id_type' => 'nin', 'id_number' => 'SAMPLE-PRIVATE'],
                'submitted_at' => now()->subDay(),
            ],
        );

        if (! app()->environment('production')) {
            $sampleDocument = "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\n%%EOF\n";
            $samplePath = 'verification/preview/pending-agent-id.pdf';
            Storage::disk('local')->put($samplePath, $sampleDocument);
            VerificationDocument::query()->updateOrCreate(
                [
                    'verification_request_id' => $pendingVerification->id,
                    'document_type' => 'government_id',
                    'storage_path' => $samplePath,
                ],
                [
                    'original_filename' => 'pending-agent-id.pdf',
                    'mime_type' => 'application/pdf',
                    'size_bytes' => strlen($sampleDocument),
                    'checksum' => hash('sha256', $sampleDocument),
                    'status' => 'uploaded',
                ],
            );
        }

        Invitation::query()->updateOrCreate(
            ['token_hash' => hash('sha256', 'preview-landlord-invitation')],
            [
                'invited_by' => $agents->first()->user_id,
                'name' => 'Kemi Landlord',
                'email' => 'kemi.landlord@example.test',
                'intended_role' => 'landlord',
                'status' => 'pending',
                'expires_at' => now()->addDays(6),
            ],
        );
        Invitation::query()->updateOrCreate(
            ['token_hash' => hash('sha256', 'preview-tenant-invitation')],
            [
                'invited_by' => $agents->first()->user_id,
                'name' => 'Samuel Tenant',
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'intended_role' => 'tenant',
                'status' => 'accepted',
                'expires_at' => now()->addDays(6),
                'accepted_at' => now()->subHours(3),
                'accepted_by' => $tenant->id,
            ],
        );

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

            $property = Property::query()->updateOrCreate(['slug' => str($title)->slug().'-'.($index + 1)], [
                'agent_id' => $agents[$index % $agents->count()]->id,
                'title' => $title,
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
                $property->images()->updateOrCreate(['sort_order' => $imageIndex], [
                    'image_path' => "/images/properties/{$typeKey}-{$variant}.webp",
                    'thumbnail_path' => "/images/properties/{$typeKey}-{$variant}-thumb.webp",
                    'alt_text' => "Property view of {$title}",
                    'sort_order' => $imageIndex,
                    'is_cover' => $imageIndex === 0,
                ]);
            }

            foreach ([$amenities[$index % 5], $amenities[($index + 1) % 5], $amenities[($index + 2) % 5]] as [$key, $label]) {
                $property->amenities()->updateOrCreate(['amenity_key' => $key], ['amenity_label' => $label]);
            }
        }
    }
}
