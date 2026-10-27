<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_guest_navigation_for_unauthenticated_users(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('guest-shell');
        $response->assertSee('Masuk');
        $response->assertSee('Daftar');
        $response->assertSee(route('login'));
        $response->assertSee(route('register'));
        $response->assertDontSee('id="workspace-sidebar"', false);
        $response->assertDontSee('id="sidebar-toggle-mobile"', false);
        $response->assertDontSee('Buka Dashboard');
    }

    public function test_landing_page_hides_sidebar_and_shows_dashboard_action_for_authenticated_users(): void
    {
        $user = User::factory()->create([
            'role' => 'lecturer',
            'name' => 'Dr. H. Mulyadi',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertSee('guest-shell');
        $response->assertDontSee('workspace-shell');
        $response->assertDontSee('id="workspace-sidebar"', false);
        $response->assertDontSee('id="sidebar-toggle-mobile"', false);
        $response->assertSee('Buka Dashboard');
        $response->assertSee(route('dashboard'));
        $response->assertSee('Dr. H. Mulyadi');
        $response->assertSee('Keluar');
        $response->assertDontSee(route('login'));
        $response->assertDontSee(route('register'));
    }

    public function test_dashboard_retains_workspace_sidebar_for_authenticated_users(): void
    {
        $user = User::factory()->create([
            'role' => 'lecturer',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('workspace-shell');
        $response->assertSee('id="workspace-sidebar"', false);
        $response->assertSee('id="sidebar-toggle-mobile"', false);
        $response->assertDontSee('guest-shell');
    }
}
