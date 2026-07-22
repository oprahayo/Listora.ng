<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivateWorkspaceLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_dashboards_do_not_render_marketplace_chrome(): void
    {
        foreach ([
            'agent' => 'agent.dashboard',
            'landlord' => 'landlord.dashboard',
            'tenant' => 'tenant.dashboard',
            'admin' => 'admin.dashboard',
        ] as $role => $routeName) {
            $response = $this->actingAs($this->roleUser($role))->withSession(['active_role' => $role])->get(route($routeName));
            $response->assertOk()
                ->assertDontSee('Search properties')
                ->assertDontSee('Browse properties in Lagos')
                ->assertDontSee('Footer navigation')
                ->assertDontSee('Commercial')
                ->assertSee('private-app-content', false);
        }
    }

    public function test_each_role_renders_its_approved_navigation(): void
    {
        $this->actingAs($this->roleUser('agent'))->withSession(['active_role' => 'agent'])->get(route('agent.dashboard'))
            ->assertOk()->assertSeeInOrder(['Home', 'Properties', 'People', 'More']);

        $this->actingAs($this->roleUser('landlord'))->withSession(['active_role' => 'landlord'])->get(route('landlord.dashboard'))
            ->assertOk()->assertSeeInOrder(['Home', 'Properties', 'Reports', 'More']);

        $this->actingAs($this->roleUser('tenant'))->withSession(['active_role' => 'tenant'])->get(route('tenant.dashboard'))
            ->assertOk()->assertSeeInOrder(['Home', 'Bills', 'Support', 'More'])
            ->assertDontSee('Saved');

        $this->actingAs($this->roleUser('admin'))->withSession(['active_role' => 'admin'])->get(route('admin.dashboard'))
            ->assertOk()->assertSeeInOrder(['Overview', 'Verifications', 'Users', 'More']);
    }

    public function test_role_switch_immediately_changes_private_navigation(): void
    {
        $user = $this->roleUser('agent');
        $user->assignRole('tenant');

        $this->actingAs($user)->withSession(['active_role' => 'agent'])
            ->post(route('workspace.switch'), ['role' => 'tenant'])
            ->assertRedirect(route('dashboard'));

        $this->get(route('tenant.dashboard'))->assertOk()
            ->assertSeeInOrder(['Home', 'Bills', 'Support', 'More'])
            ->assertDontSee('People');
        $this->assertSame('tenant', $user->fresh()->last_active_role);
    }

    public function test_guests_and_other_roles_cannot_access_private_workspaces(): void
    {
        $this->get(route('tenant.dashboard'))->assertRedirect();

        $tenant = $this->roleUser('tenant');
        $this->actingAs($tenant)->withSession(['active_role' => 'tenant'])
            ->get(route('agent.dashboard'))->assertForbidden();
        $this->get(route('admin.verifications.index'))->assertForbidden();
    }

    public function test_private_inner_pages_include_the_app_shell_and_bottom_navigation_clearance(): void
    {
        $tenant = $this->roleUser('tenant');
        $this->actingAs($tenant)->withSession(['active_role' => 'tenant'])
            ->get(route('tenant.bills'))->assertOk()
            ->assertSee('No bills yet.')
            ->assertSee('private-app-content', false)
            ->assertSee('private-bottom-nav', false)
            ->assertDontSee('Search location or property');
    }

    public function test_public_pages_keep_public_navigation_and_universal_login(): void
    {
        $home = $this->get('/')->assertOk()
            ->assertSee('Main navigation')
            ->assertSee('Search properties')
            ->getContent();

        $this->assertStringContainsString('Email or phone', (string) $home);
        $this->assertStringNotContainsString('Choose account role', (string) $home);
        $this->assertStringNotContainsString('role="tablist"', (string) $home);
    }

    public function test_no_private_view_uses_the_public_layout_or_exposes_sprint_wording(): void
    {
        $directories = ['dashboards', 'admin', 'onboarding', 'notifications', 'workspace'];
        foreach ($directories as $directory) {
            foreach (glob(resource_path("views/{$directory}/*.blade.php")) ?: [] as $file) {
                $contents = file_get_contents($file);
                $this->assertStringNotContainsString('<x-layouts.public>', $contents, $file);
                $this->assertDoesNotMatchRegularExpression('/Sprint\s*\d|roadmap|future sprint/i', $contents, $file);
            }
        }
    }

    private function roleUser(string $role): User
    {
        $user = User::factory()->create([
            'primary_role' => $role,
            'phone_verified_at' => now(),
            'status' => 'active',
        ]);
        $user->assignRole($role);

        return $user;
    }
}
