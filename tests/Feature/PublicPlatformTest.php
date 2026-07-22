<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads_with_featured_listings_and_login_modal(): void
    {
        $this->seed();

        $this->get('/')
            ->assertOk()
            ->assertSee('Find your next property.')
            ->assertSee('Featured properties')
            ->assertSee('Sign in')
            ->assertSee('role="dialog"', false);
    }

    public function test_listings_can_be_searched_and_filtered(): void
    {
        $this->seed();

        $this->get('/properties?city=Lagos&type=apartment&max_price=3000000&bedrooms=2')
            ->assertOk()
            ->assertSee('Bright Apartment in Yaba')
            ->assertDontSee('Quiet Self Contain in Gwarinpa');

        $this->get('/properties?q=Bodija&amenities[]=security')
            ->assertOk()
            ->assertSee('Bodija');
    }

    public function test_only_published_properties_are_public(): void
    {
        $agent = Agent::factory()->create();
        $published = Property::factory()->for($agent)->create(['title' => 'Public Home', 'slug' => 'public-home']);
        $draft = Property::factory()->for($agent)->draft()->create(['title' => 'Hidden Draft', 'slug' => 'hidden-draft']);
        $archived = Property::factory()->for($agent)->archived()->create(['title' => 'Hidden Archive', 'slug' => 'hidden-archive']);

        foreach ([$published, $draft, $archived] as $property) {
            PropertyImage::factory()->for($property)->create();
        }

        $this->get('/properties')
            ->assertOk()
            ->assertSee('Public Home')
            ->assertDontSee('Hidden Draft')
            ->assertDontSee('Hidden Archive');

        $this->get('/properties/hidden-draft')->assertNotFound();
        $this->get('/properties/hidden-archive')->assertNotFound();
    }

    public function test_property_detail_loads_by_slug_and_invalid_slug_returns_404(): void
    {
        $this->seed();
        $property = Property::query()->published()->firstOrFail();

        $this->get(route('properties.show', $property))
            ->assertOk()
            ->assertSee($property->title)
            ->assertSee('Book Inspection')
            ->assertSee('Chat with Agent')
            ->assertSee('application/ld+json', false);

        $this->get('/properties/not-a-real-property')->assertNotFound();
    }

    public function test_guest_saved_summary_returns_only_public_properties(): void
    {
        $agent = Agent::factory()->create();
        $published = Property::factory()->for($agent)->create();
        $draft = Property::factory()->for($agent)->draft()->create();
        PropertyImage::factory()->for($published)->create();
        PropertyImage::factory()->for($draft)->create();

        $this->getJson('/saved/property-summaries?ids[]='.$published->id.'&ids[]='.$draft->id.'&ids[]=99999')
            ->assertOk()
            ->assertJsonCount(1, 'properties')
            ->assertJsonPath('properties.0.id', $published->id)
            ->assertJsonPath('valid_ids.0', $published->id);
    }

    public function test_pwa_and_seo_resources_are_available(): void
    {
        $this->seed();

        $this->get('/manifest.webmanifest')->assertOk()->assertHeader('content-type', 'application/manifest+json');
        $this->get('/service-worker.js')->assertOk()->assertHeader('content-type', 'application/javascript');
        $serviceWorker = file_get_contents(public_path('service-worker.js'));
        $this->assertStringContainsString("const VERSION = 'v4';", $serviceWorker);
        $this->assertStringContainsString('networkFirstPage(request)', $serviceWorker);
        $this->assertStringContainsString('caches.delete(key)', $serviceWorker);
        $this->assertStringContainsString('self.clients.claim()', $serviceWorker);
        $this->assertStringContainsString('self.skipWaiting()', $serviceWorker);
        $this->assertStringNotContainsString('sprint', strtolower($serviceWorker));
        $this->get('/offline')->assertOk()->assertSee('You’re offline');
        $this->get('/sitemap.xml')->assertOk()->assertSee('<urlset', false);
    }

    public function test_public_pages_do_not_expose_internal_delivery_language(): void
    {
        $this->seed();
        $property = Property::query()->published()->firstOrFail();
        $output = collect([
            '/',
            '/properties',
            route('properties.show', $property, false),
            '/saved',
            '/join',
            '/forgot-password',
            '/offline',
        ])->map(fn (string $url) => strtolower((string) $this->get($url)->assertOk()->getContent()))->join("\n");

        foreach ([
            'sprint 1',
            'sprint 2',
            'sprint 5',
            'development otp',
            'future sprint',
            'roadmap',
            'placeholder implementation',
            'demonstration',
            'no booking has been created',
        ] as $forbiddenCopy) {
            $this->assertStringNotContainsString($forbiddenCopy, $output);
        }
    }
}
