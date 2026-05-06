<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_navigation_uses_grouped_submenus(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-dashboard-subnav-toggle', false)
            ->assertSee('Operations')
            ->assertSee('Content')
            ->assertSee('Audit Log')
            ->assertSee('dashboard-shell__nav-icon', false);
    }

    public function test_active_submenu_is_expanded_for_nested_routes(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
            'status' => 'active',
        ]);

        $this->actingAs($seller)
            ->get(route('dashboard.seller.listings'))
            ->assertOk()
            ->assertSee('Listing Management')
            ->assertSee('aria-expanded="true"', false)
            ->assertSee('aria-current="page"', false);
    }
}
