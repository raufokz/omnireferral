<?php

namespace Tests\Feature;

use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentDirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function createPublishedAgent(array $profileOverrides = [], array $userOverrides = []): RealtorProfile
    {
        $agent = User::factory()->create(array_merge([
            'name' => 'Taylor Agent',
            'role' => 'agent',
            'status' => 'active',
        ], $userOverrides));

        return RealtorProfile::updateOrCreate(['user_id' => $agent->id], array_merge([
            'slug' => 'taylor-agent',
            'brokerage_name' => 'Premier Realty',
            'service_city' => 'Dallas',
            'service_state' => 'TX',
            'specialties' => 'Buyer Representation',
            'bio' => 'Experienced Dallas agent helping buyers and sellers across North Texas with responsive communication.',
            'headshot' => 'images/about/about-omnireferral.svg',
            'rating' => 4.5,
            'profile_status' => RealtorProfile::STATUS_PUBLISHED,
        ], $profileOverrides));
    }

    public function test_agents_page_lists_published_profiles(): void
    {
        $this->createPublishedAgent();

        $this->get(route('agents.index'))
            ->assertOk()
            ->assertSee('Taylor Agent')
            ->assertSee('Premier Realty')
            ->assertDontSee('Verified Agent');
    }

    public function test_draft_profiles_are_hidden(): void
    {
        $this->createPublishedAgent(['slug' => 'visible-agent']);

        $draftUser = User::factory()->create(['name' => 'Draft Only', 'role' => 'agent']);
        RealtorProfile::where('user_id', $draftUser->id)->update([
            'slug' => 'draft-only',
            'brokerage_name' => 'Hidden Brokerage',
            'service_city' => 'Houston',
            'service_state' => 'TX',
            'bio' => 'Should not appear in the public directory listing results for buyers.',
            'profile_status' => RealtorProfile::STATUS_DRAFT,
        ]);

        $this->get(route('agents.index'))
            ->assertOk()
            ->assertSee('Taylor Agent')
            ->assertDontSee('Draft Only')
            ->assertDontSee('Hidden Brokerage');
    }

    public function test_featured_agents_appear_with_badge(): void
    {
        $this->createPublishedAgent([
            'slug' => 'featured-agent',
            'profile_status' => RealtorProfile::STATUS_FEATURED,
        ]);

        $this->get(route('agents.index'))
            ->assertOk()
            ->assertSee('Featured Agent');
    }

    public function test_agent_seo_page_is_public_for_published_profiles(): void
    {
        $profile = $this->createPublishedAgent(['slug' => 'seo-agent-slug']);

        $response = $this->get(route('agents.profile', $profile));

        $response->assertOk()
            ->assertSee('Taylor Agent')
            ->assertSee('Premier Realty');
        $this->assertStringNotContainsString((string) $profile->user?->email, $response->getContent());
    }

    public function test_agent_inquiry_creates_admin_routed_lead(): void
    {
        $profile = $this->createPublishedAgent(['slug' => 'inquiry-agent']);

        $this->postJson(route('agents.inquiry', $profile), [
            'inquiry_type' => 'contact',
            'name' => 'Buyer Person',
            'email' => 'buyer@example.com',
            'phone' => '5551234567',
            'city' => 'Dallas',
            'message' => 'I need help buying a home in Dallas this spring.',
            'property_requirements' => '3 bed, 2 bath, under 500k',
        ])->assertOk();

        $this->assertDatabaseHas('contacts', [
            'email' => 'buyer@example.com',
            'realtor_profile_id' => $profile->id,
            'recipient_user_id' => null,
            'source' => 'agent_directory_contact',
        ]);

        $this->assertDatabaseHas('leads', [
            'email' => 'buyer@example.com',
            'source' => 'agent_directory',
        ]);
    }

    public function test_location_page_filters_by_state(): void
    {
        $this->createPublishedAgent(['service_state' => 'TX', 'service_city' => 'Dallas']);
        $this->createPublishedAgent([
            'slug' => 'miami-agent',
            'service_state' => 'FL',
            'service_city' => 'Miami',
        ], ['name' => 'Miami Agent', 'email' => 'miami@example.com']);

        $this->get(route('agents.location', 'texas'))
            ->assertOk()
            ->assertSee('Dallas')
            ->assertDontSee('Miami Agent');
    }

    public function test_join_as_agent_redirects_to_directory(): void
    {
        $this->get('/join-as-agent')->assertRedirect('/agents');
    }
}
