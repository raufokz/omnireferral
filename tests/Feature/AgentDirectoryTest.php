<?php

namespace Tests\Feature;

use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentDirectoryTest extends TestCase
{
    use RefreshDatabase;

    private function createPublicAgent(array $userOverrides = [], array $profileOverrides = []): User
    {
        $agent = User::factory()->create(array_merge([
            'name' => 'Taylor Agent',
            'email' => 'taylor.agent@example.com',
            'phone' => '(555) 123-4567',
            'role' => 'agent',
            'status' => 'active',
            'city' => 'Dallas',
            'state' => 'TX',
            'zip_code' => '75201',
        ], $userOverrides));

        RealtorProfile::updateOrCreate(['user_id' => $agent->id], array_merge([
            'slug' => 'taylor-agent',
            'brokerage_name' => 'Premier Realty',
            'license_number' => 'TX-123456',
            'service_city' => 'Dallas',
            'service_state' => 'TX',
            'service_zip_code' => '75201',
            'specialties' => 'Buyer Representation',
            'bio' => str_repeat('Experienced Dallas agent focused on responsive communication and local market expertise. ', 2),
            'headshot' => 'assets/images/default-agent-avatar.svg',
            'rating' => 4.5,
            'approved_at' => now(),
        ], $profileOverrides));

        return $agent->fresh('realtorProfile');
    }

    public function test_agents_page_lists_only_public_eligible_profiles(): void
    {
        $this->createPublicAgent();

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
            ->assertSee('Premier Realty')
            ->assertSee('Dallas')
            ->assertDontSee('Bailey Buyer')
            ->assertDontSee('Admin User');
    }

    public function test_agents_page_hides_unapproved_profiles(): void
    {
        $this->createPublicAgent([
            'name' => 'Pending Profile Agent',
            'email' => 'pending.agent@example.com',
            'city' => 'Houston',
            'state' => 'TX',
        ], [
            'slug' => 'pending-profile-agent',
            'service_city' => 'Houston',
            'service_state' => 'TX',
            'approved_at' => null,
        ]);

        $this->get(route('agents.index'))
            ->assertOk()
            ->assertDontSee('Pending Profile Agent')
            ->assertDontSee('Profile Pending');
    }

    public function test_agent_profile_page_resolves_by_slug_and_requires_approval(): void
    {
        $agent = $this->createPublicAgent([
            'name' => 'Public Agent',
        ], [
            'slug' => 'public-agent-slug',
            'brokerage_name' => 'Test Brokerage',
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

    public function test_agents_page_shows_empty_state_when_no_public_profiles_exist(): void
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

    public function test_join_as_agent_creates_pending_user_and_profile(): void
    {
        $response = $this->post(route('join-as-agent.store'), [
            'name' => 'Jordan Agent',
            'email' => 'jordan.agent@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'city' => 'Dallas',
            'state' => 'TX',
            'service_city' => 'Dallas',
            'service_state' => 'TX',
            'brokerage_name' => 'Jordan Realty',
            'specialties' => ['Buyer Representation', 'Relocation'],
            'bio' => str_repeat('Professional agent bio with enough detail for admin review and public directory readiness. ', 2),
            'terms_accepted' => '1',
        ]);

        $response->assertRedirect(route('join-as-agent.success'));

        $user = User::where('email', 'jordan.agent@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('agent', $user->role);
        $this->assertSame('pending', $user->status);

        $profile = $user->realtorProfile;
        $this->assertNotNull($profile);
        $this->assertNull($profile->approved_at);
        $this->assertSame('Pending admin review', $profile->approval_notes);
        $this->assertSame($user->id, $profile->user_id);
    }
}
