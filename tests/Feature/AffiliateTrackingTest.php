<?php

namespace Tests\Feature;

use App\Models\AffiliateProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AffiliateTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sets_affiliate_cookie_from_ref_param(): void
    {
        $response = $this->get('/?ref=AGENT123');

        $response->assertStatus(200);
        $response->assertCookie('omnireferral_affiliate', 'AGENT123');
    }

    public function test_it_links_registered_user_to_affiliate(): void
    {
        Storage::fake('public');
        Queue::fake();

        $agentUser = User::create([
            'name' => 'Agent Test',
            'email' => 'agent@test.com',
            'password' => bcrypt('password'),
            'role' => 'agent',
            'status' => 'active',
        ]);

        $affiliate = AffiliateProfile::create([
            'user_id' => $agentUser->id,
            'slug' => 'agent-test',
            'referral_code' => 'TESTCODE',
        ]);

        $this->withCookie('omnireferral_affiliate', 'TESTCODE')
             ->post('/register', [
                 'name' => 'Buyer Test',
                 'email' => 'buyer@test.com',
                 'phone' => '(555) 000-1234',
                 'address_line_1' => '100 Referral Lane',
                 'city' => 'Dallas',
                 'state' => 'TX',
                 'zip_code' => '75201',
                 'password' => 'password123',
                 'password_confirmation' => 'password123',
                 'role' => 'buyer',
                 'profile_image' => UploadedFile::fake()->createWithContent(
                     'buyer-profile.png',
                     base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+yF9sAAAAASUVORK5CYII=')
                 ),
             ])
             ->assertRedirect(route('dashboard.buyer'));

        $this->assertDatabaseHas('users', [
            'email' => 'buyer@test.com',
            'referred_by_user_id' => $agentUser->id,
        ]);

        $this->assertDatabaseHas('affiliate_profiles', [
            'referral_code' => 'TESTCODE',
            'conversion_count' => 1,
        ]);
    }
}
