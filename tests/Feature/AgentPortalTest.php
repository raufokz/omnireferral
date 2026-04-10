<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Package;
use App\Models\Property;
use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_cannot_create_more_active_listings_than_the_package_allows(): void
    {
        $package = Package::create([
            'name' => 'Quick Lead',
            'description' => 'Entry plan',
            'slug' => 'quick-leads',
            'category' => 'lead',
            'billing_type' => 'one_time',
            'is_featured' => false,
            'is_active' => true,
            'one_time_price' => 499,
            'monthly_price' => null,
            'features' => ['5 active listings'],
            'cta_label' => 'Get Started',
            'duration_days' => 365,
            'sort_order' => 1,
        ]);

        $agent = User::factory()->create([
            'role' => 'agent',
            'status' => 'active',
            'current_plan_id' => $package->id,
        ]);

        $profile = RealtorProfile::create([
            'user_id' => $agent->id,
            'slug' => 'agent-portal-test',
            'brokerage_name' => 'Prime Brokerage',
            'license_number' => 'TX-111111',
            'address_line_1' => '100 Main Street',
            'city' => 'Dallas',
            'state' => 'TX',
            'zip_code' => '75201',
            'rating' => 4.9,
            'review_count' => 10,
            'leads_closed' => 3,
            'specialties' => 'Buyer Representation',
            'bio' => 'Agent bio',
            'headshot' => 'images/realtors/3.png',
        ]);

        foreach (range(1, 5) as $number) {
            Property::create([
                'title' => 'Listing ' . $number,
                'description' => 'Test listing',
                'slug' => 'listing-' . $number,
                'status' => 'Active',
                'property_type' => 'house',
                'price' => 300000 + $number,
                'location' => 'Dallas, TX',
                'zip_code' => '75201',
                'beds' => 3,
                'baths' => 2,
                'sqft' => 1600,
                'source' => 'Agent Dashboard Upload',
                'is_featured' => false,
                'published_at' => now(),
                'realtor_profile_id' => $profile->id,
            ]);
        }

        $this->actingAs($agent)
            ->post(route('agent.listings.store'), [
                'title' => 'Listing 6',
                'location' => 'Dallas, TX',
                'zip_code' => '75201',
                'property_type' => 'house',
                'price' => 400000,
            ])
            ->assertRedirect(route('agent.listings.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('properties', [
            'title' => 'Listing 6',
        ]);
    }

    public function test_property_inquiry_is_saved_to_the_assigned_agents_message_inbox(): void
    {
        $agent = User::factory()->create([
            'name' => 'Jordan Agent',
            'email' => 'jordan@example.com',
            'role' => 'agent',
            'status' => 'active',
        ]);

        $profile = RealtorProfile::create([
            'user_id' => $agent->id,
            'slug' => 'jordan-agent',
            'brokerage_name' => 'North Star Realty',
            'license_number' => 'AZ-222222',
            'address_line_1' => '50 Desert Ave',
            'city' => 'Phoenix',
            'state' => 'AZ',
            'zip_code' => '85001',
            'rating' => 4.8,
            'review_count' => 14,
            'leads_closed' => 6,
            'specialties' => 'Luxury, Relocation',
            'bio' => 'Experienced local guide.',
            'headshot' => 'images/realtors/3.png',
        ]);

        $property = Property::create([
            'title' => 'Sunny Family Retreat',
            'description' => 'Property description',
            'slug' => 'sunny-family-retreat',
            'status' => 'Active',
            'property_type' => 'house',
            'price' => 495000,
            'location' => 'Phoenix, AZ',
            'zip_code' => '85001',
            'beds' => 4,
            'baths' => 2,
            'sqft' => 1830,
            'source' => 'Agent Dashboard Upload',
            'is_featured' => false,
            'published_at' => now(),
            'realtor_profile_id' => $profile->id,
        ]);

        $this->from(route('properties.show', $property))
            ->post(route('contact.submit'), [
                'name' => 'Taylor Buyer',
                'email' => 'buyer@example.com',
                'phone' => '(555) 444-1212',
                'subject' => 'Inquiry about Sunny Family Retreat',
                'message' => 'I would like to schedule a showing this weekend.',
                'property_id' => $property->id,
                'realtor_profile_id' => $profile->id,
                'recipient_user_id' => $agent->id,
                'source' => 'website_property_inquiry',
            ])
            ->assertRedirect(route('properties.show', $property))
            ->assertSessionHas('success');

        $contact = Contact::firstOrFail();

        $this->assertSame($agent->id, $contact->recipient_user_id);
        $this->assertSame($profile->id, $contact->realtor_profile_id);
        $this->assertSame($property->id, $contact->property_id);
        $this->assertSame('new', $contact->message_status);

        $this->actingAs($agent)
            ->get(route('agent.messages.index'))
            ->assertOk()
            ->assertSee('Taylor Buyer')
            ->assertSee('Sunny Family Retreat')
            ->assertSee('Inquiry about Sunny Family Retreat');
    }

    public function test_agent_can_open_each_portal_page(): void
    {
        $package = Package::create([
            'name' => 'Power Lead',
            'description' => 'Growth plan',
            'slug' => 'power-leads',
            'category' => 'lead',
            'billing_type' => 'one_time',
            'is_featured' => true,
            'is_active' => true,
            'one_time_price' => 797,
            'monthly_price' => null,
            'features' => ['15 active listings'],
            'cta_label' => 'Get Started',
            'duration_days' => 365,
            'sort_order' => 2,
        ]);

        $agent = User::factory()->create([
            'name' => 'Portal Agent',
            'role' => 'agent',
            'status' => 'active',
            'current_plan_id' => $package->id,
            'phone' => '(555) 200-3000',
            'city' => 'Dallas',
            'state' => 'TX',
            'zip_code' => '75201',
        ]);

        RealtorProfile::create([
            'user_id' => $agent->id,
            'slug' => 'portal-agent',
            'brokerage_name' => 'Portal Realty',
            'license_number' => 'TX-333333',
            'address_line_1' => '10 Oak Street',
            'city' => 'Dallas',
            'state' => 'TX',
            'zip_code' => '75201',
            'rating' => 4.7,
            'review_count' => 9,
            'leads_closed' => 4,
            'specialties' => 'Listings, Buyer Representation',
            'bio' => 'Portal bio',
            'headshot' => 'images/realtors/3.png',
        ]);

        $this->actingAs($agent)->get(route('dashboard.agent'))->assertOk();
        $this->actingAs($agent)->get(route('agent.profile'))->assertOk();
        $this->actingAs($agent)->get(route('agent.leads.index'))->assertOk();
        $this->actingAs($agent)->get(route('agent.listings.index'))->assertOk();
        $this->actingAs($agent)->get(route('agent.messages.index'))->assertOk();
    }
}
