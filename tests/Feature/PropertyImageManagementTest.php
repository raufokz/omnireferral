<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Property;
use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertyImageManagementTest extends TestCase
{
    use RefreshDatabase;

    private function fakePngUpload(string $name): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+yF9sAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($name, $png);
    }

    private function createAgentWithListingAccess(): array
    {
        $package = Package::create([
            'name' => 'Power Lead',
            'description' => 'Listing enabled plan',
            'slug' => 'power-leads',
            'category' => 'lead',
            'billing_type' => 'one_time',
            'is_featured' => true,
            'is_active' => true,
            'one_time_price' => 799,
            'monthly_price' => null,
            'features' => ['15 active listings'],
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
            'slug' => 'image-manager-agent',
            'brokerage_name' => 'Gallery Realty',
            'license_number' => 'TX-121212',
            'address_line_1' => '10 Main Street',
            'city' => 'Dallas',
            'state' => 'TX',
            'zip_code' => '75201',
            'rating' => 4.8,
            'review_count' => 12,
            'leads_closed' => 4,
            'specialties' => 'Listings',
            'bio' => 'Agent bio',
            'headshot' => 'images/realtors/3.png',
        ]);

        return [$agent, $profile];
    }

    public function test_agent_can_create_listing_with_reordered_gallery_and_featured_image(): void
    {
        Storage::fake('public');
        [$agent] = $this->createAgentWithListingAccess();

        $response = $this->actingAs($agent)->post(route('agent.listings.store'), [
            'title' => 'Gallery Order Home',
            'location' => 'Dallas, TX',
            'zip_code' => '75201',
            'property_type' => 'house',
            'price' => 450000,
            'images' => [
                $this->fakePngUpload('front.png'),
                $this->fakePngUpload('kitchen.png'),
                $this->fakePngUpload('patio.png'),
            ],
            'new_upload_tokens' => [
                'new::front',
                'new::kitchen',
                'new::patio',
            ],
            'gallery_order' => [
                'new::patio',
                'new::front',
                'new::kitchen',
            ],
            'featured_image' => 'new::kitchen',
        ]);

        $response
            ->assertRedirect(route('agent.listings.index'));

        $property = Property::query()->latest('id')->firstOrFail();

        $this->assertCount(3, $property->images);
        $this->assertSame($property->images[2], $property->image);
        $this->assertStringStartsWith('properties/listings/', $property->images[0]);
        $this->assertStringStartsWith('properties/listings/', $property->images[1]);
        $this->assertStringStartsWith('properties/listings/', $property->images[2]);
        $this->assertNotSame($property->images[0], $property->images[1]);
        $this->assertNotSame($property->images[1], $property->images[2]);

        foreach ($property->images as $storedPath) {
            Storage::disk('public')->assertExists($storedPath);
        }
    }

    public function test_owner_can_reorder_remove_and_refeature_existing_gallery_images(): void
    {
        Storage::fake('public');
        [$agent, $profile] = $this->createAgentWithListingAccess();

        $first = $this->fakePngUpload('first.png')->store('properties/listings', 'public');
        $second = $this->fakePngUpload('second.png')->store('properties/listings', 'public');
        $third = $this->fakePngUpload('third.png')->store('properties/listings', 'public');

        $property = Property::create([
            'title' => 'Editable Gallery Home',
            'description' => 'Description',
            'slug' => 'editable-gallery-home',
            'status' => 'Active',
            'approval_status' => Property::APPROVAL_APPROVED,
            'property_type' => 'house',
            'price' => 400000,
            'location' => 'Dallas, TX',
            'zip_code' => '75201',
            'beds' => 3,
            'baths' => 2,
            'sqft' => 1800,
            'source' => 'Agent Dashboard Upload',
            'published_at' => now(),
            'realtor_profile_id' => $profile->id,
            'owner_user_id' => $agent->id,
            'image' => $first,
            'images' => [$first, $second, $third],
        ]);

        $response = $this->actingAs($agent)->put(route('properties.update', $property), [
            'title' => $property->title,
            'location' => $property->location,
            'price' => $property->price,
            'status' => 'Active',
            'description' => $property->description,
            'existing_images' => [$third, $first],
            'remove_images' => [$second],
            'gallery_order' => ['existing::' . $third, 'existing::' . $first],
            'featured_image' => 'existing::' . $third,
        ]);

        $response
            ->assertRedirect(route('agent.listings.index'))
            ->assertSessionHas('success');

        $property->refresh();

        $this->assertSame([$third, $first], $property->images);
        $this->assertSame($third, $property->image);
        Storage::disk('public')->assertMissing($second);
        Storage::disk('public')->assertExists($first);
        Storage::disk('public')->assertExists($third);
    }
}
