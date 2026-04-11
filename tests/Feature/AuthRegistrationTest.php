<?php

namespace Tests\Feature;

use App\Jobs\SyncUserToGoHighLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function fakePngUpload(string $name = 'profile.png'): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+yF9sAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($name, $png);
    }

    public function test_agent_registration_requires_full_profile_details_and_creates_a_realtor_profile(): void
    {
        Storage::fake('public');
        Queue::fake();

        $this->post(route('register'), [
            'role' => 'agent',
            'name' => 'Taylor Morgan',
            'email' => 'taylor@example.com',
            'phone' => '(555) 111-2222',
            'address_line_1' => '123 Main Street',
            'address_line_2' => 'Suite 400',
            'city' => 'Dallas',
            'state' => 'tx',
            'zip_code' => '75201',
            'brokerage_name' => 'Premier Realty Group',
            'license_number' => 'TX-1234567',
            'password' => 'super-secret-password',
            'password_confirmation' => 'super-secret-password',
            'profile_image' => $this->fakePngUpload('agent-profile.png'),
        ])
            ->assertRedirect(route('dashboard.agent'))
            ->assertSessionHas('success');

        $this->assertAuthenticated();

        $user = User::where('email', 'taylor@example.com')->firstOrFail();

        $this->assertSame('agent', $user->role);
        $this->assertSame('(555) 111-2222', $user->phone);
        $this->assertSame('123 Main Street', $user->address_line_1);
        $this->assertSame('Suite 400', $user->address_line_2);
        $this->assertSame('Dallas', $user->city);
        $this->assertSame('TX', $user->state);
        $this->assertSame('75201', $user->zip_code);
        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);

        $profile = $user->realtorProfile()->first();

        $this->assertNotNull($profile);
        $this->assertSame('Premier Realty Group', $profile->brokerage_name);
        $this->assertSame('TX-1234567', $profile->license_number);
        $this->assertSame('123 Main Street', $profile->address_line_1);
        $this->assertSame('Suite 400', $profile->address_line_2);
        $this->assertSame('Dallas', $profile->city);
        $this->assertSame('TX', $profile->state);
        $this->assertSame('75201', $profile->zip_code);

        Queue::assertPushed(SyncUserToGoHighLevel::class);
    }

    public function test_buyer_registration_still_requires_core_profile_fields_but_not_agent_credentials(): void
    {
        Storage::fake('public');
        Queue::fake();

        $this->post(route('register'), [
            'role' => 'buyer',
            'name' => 'Jamie Carter',
            'email' => 'jamie@example.com',
            'phone' => '(555) 333-4444',
            'address_line_1' => '80 Market Street',
            'city' => 'Miami',
            'state' => 'FL',
            'zip_code' => '33101',
            'password' => 'super-secret-password',
            'password_confirmation' => 'super-secret-password',
            'profile_image' => $this->fakePngUpload('buyer-profile.png'),
        ])
            ->assertRedirect(route('dashboard.buyer'));

        $user = User::where('email', 'jamie@example.com')->firstOrFail();

        $this->assertSame('buyer', $user->role);
        $this->assertNull($user->realtorProfile);
        Queue::assertPushed(SyncUserToGoHighLevel::class);
    }

    public function test_legacy_onboarding_routes_redirect_to_login_for_guests(): void
    {
        $this->get(route('onboarding', 'agent'))
            ->assertRedirect(route('login'));

        $this->get(route('client.form.submission', ['role' => 'agent']))
            ->assertRedirect(route('login'));
    }
}
