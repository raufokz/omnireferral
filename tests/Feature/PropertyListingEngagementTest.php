<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureListingDeviceCookie;
use App\Models\Property;
use App\Models\PropertyComment;
use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyListingEngagementTest extends TestCase
{
    use RefreshDatabase;

    private function sampleProperty(): Property
    {
        $agent = User::factory()->create([
            'role' => 'agent',
            'status' => 'active',
        ]);

        $profile = RealtorProfile::create([
            'user_id' => $agent->id,
            'slug' => 'engage-agent',
            'brokerage_name' => 'Engage Realty',
            'license_number' => 'TX-999999',
            'address_line_1' => '1 Test Way',
            'city' => 'Dallas',
            'state' => 'TX',
            'zip_code' => '75201',
            'rating' => 4.9,
            'review_count' => 1,
            'leads_closed' => 1,
            'specialties' => 'Listings',
            'bio' => 'Bio',
            'headshot' => 'images/realtors/3.png',
        ]);

        return Property::create([
            'title' => 'Engagement Test Home',
            'description' => 'Desc',
            'slug' => 'engagement-test-home',
            'status' => 'Active',
            'approval_status' => Property::APPROVAL_APPROVED,
            'property_type' => 'house',
            'price' => 400000,
            'location' => 'Dallas, TX',
            'zip_code' => '75201',
            'beds' => 3,
            'baths' => 2,
            'sqft' => 1600,
            'source' => 'Agent Dashboard Upload',
            'is_featured' => false,
            'published_at' => now(),
            'realtor_profile_id' => $profile->id,
            'owner_user_id' => $agent->id,
        ]);
    }

    public function test_guest_can_like_once_per_device_cookie_and_remove(): void
    {
        $property = $this->sampleProperty();
        $device = str_repeat('a', 64);

        $this->withCookie(EnsureListingDeviceCookie::COOKIE_NAME, $device)
            ->from(route('properties.show', $property))
            ->post(route('properties.favorite.toggle', $property))
            ->assertRedirect();

        $this->assertDatabaseHas('property_favorites', [
            'property_id' => $property->id,
            'device_fingerprint' => $device,
            'user_id' => null,
        ]);

        $this->withCookie(EnsureListingDeviceCookie::COOKIE_NAME, $device)
            ->from(route('properties.show', $property))
            ->post(route('properties.favorite.toggle', $property))
            ->assertRedirect();

        $this->assertDatabaseMissing('property_favorites', [
            'property_id' => $property->id,
            'device_fingerprint' => $device,
        ]);
    }

    public function test_guest_can_post_a_property_comment(): void
    {
        $property = $this->sampleProperty();

        $this->from(route('properties.show', $property))
            ->post(route('properties.comments.store', $property), [
                'author_name' => 'Casey Guest',
                'body' => 'Beautiful curb appeal from the photos.',
            ])
            ->assertRedirect(route('properties.show', $property));

        $this->assertDatabaseHas('property_comments', [
            'property_id' => $property->id,
            'author_name' => 'Casey Guest',
            'user_id' => null,
        ]);
    }

    public function test_authenticated_user_comment_does_not_require_author_name(): void
    {
        $property = $this->sampleProperty();
        $buyer = User::factory()->create([
            'role' => 'buyer',
            'status' => 'active',
            'name' => 'Logged In Pat',
        ]);

        $this->actingAs($buyer)
            ->from(route('properties.show', $property))
            ->post(route('properties.comments.store', $property), [
                'body' => 'Posting as a signed-in buyer.',
            ])
            ->assertRedirect(route('properties.show', $property));

        $comment = PropertyComment::firstOrFail();
        $this->assertSame($buyer->id, (int) $comment->user_id);
        $this->assertSame('Logged In Pat', $comment->displayAuthor());
    }
}
