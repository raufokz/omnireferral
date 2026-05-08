<?php

namespace Tests\Feature;

use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_agents_page_lists_only_users_with_agent_role(): void
    {
        $agent = User::factory()->create([
            'name' => 'Taylor Agent',
            'email' => 'taylor.agent@example.com',
            'phone' => '(555) 123-4567',
            'role' => 'agent',
            'status' => 'active',
            'city' => 'Dallas',
            'state' => 'TX',
            'zip_code' => '75201',
        ]);

        RealtorProfile::updateOrCreate(['user_id' => $agent->id], [
            'slug' => 'taylor-agent',
            'brokerage_name' => 'Premier Realty',
            'license_number' => 'TX-123456',
            'service_city' => 'Dallas',
            'service_state' => 'TX',
            'service_zip_code' => '75201',
            'specialties' => 'Buyer Representation',
            'bio' => 'Local agent bio.',
            'headshot' => 'images/realtors/1.png',
            'approved_at' => now(),
        ]);

        User::factory()->create([
            'name' => 'Bailey Buyer',
            'email' => 'bailey.buyer@example.com',
            'role' => 'buyer',
            'status' => 'active',
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin.user@example.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->get(route('agents.index'))
            ->assertOk()
            ->assertSee('Taylor Agent')
            ->assertSee('taylor.agent@example.com')
            ->assertSee('(555) 123-4567')
            ->assertSee('Dallas')
            ->assertSee('Premier Realty')
            ->assertDontSee('Bailey Buyer')
            ->assertDontSee('Admin User');
    }

    public function test_agents_page_lists_active_agent_users_even_without_approved_profile(): void
    {
        $pending = User::factory()->create([
            'name' => 'Pending Profile Agent',
            'email' => 'pending.agent@example.com',
            'role' => 'agent',
            'status' => 'active',
            'city' => 'Houston',
            'state' => 'TX',
        ]);

        RealtorProfile::updateOrCreate(['user_id' => $pending->id], [
            'slug' => 'pending-profile-agent',
            'brokerage_name' => 'Open Brokerage',
            'service_city' => 'Houston',
            'service_state' => 'TX',
            'specialties' => 'Listings',
            'approved_at' => null,
        ]);

        $this->get(route('agents.index'))
            ->assertOk()
            ->assertSee('Pending Profile Agent')
            ->assertSee('pending.agent@example.com')
            ->assertSee('Profile Pending');
    }

    public function test_agent_profile_page_resolves_by_slug_and_requires_approval(): void
    {
        $agent = User::factory()->create([
            'name' => 'Public Agent',
            'role' => 'agent',
            'status' => 'active',
        ]);

        RealtorProfile::updateOrCreate(['user_id' => $agent->id], [
            'slug' => 'public-agent-slug',
            'brokerage_name' => 'Test Brokerage',
            'approved_at' => now(),
            'service_city' => 'Austin',
            'service_state' => 'TX',
        ]);

        $this->get(route('agents.show', ['realtor' => 'public-agent-slug']))
            ->assertOk()
            ->assertSee('Public Agent')
            ->assertSee('Test Brokerage');

        RealtorProfile::where('user_id', $agent->id)->update(['approved_at' => null]);

        $this->get(route('agents.show', ['realtor' => 'public-agent-slug']))
            ->assertNotFound();
    }

    public function test_agents_page_shows_empty_state_when_no_agent_users_exist(): void
    {
        User::factory()->create([
            'name' => 'Seller User',
            'email' => 'seller@example.com',
            'role' => 'seller',
        ]);

        $this->get(route('agents.index'))
            ->assertOk()
            ->assertSee('No Agents Found')
            ->assertDontSee('seller@example.com');
    }
}
