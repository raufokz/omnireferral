<?php

namespace Tests\Feature;

use App\Models\AffiliateProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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

    public function test_affiliate_cookie_tracks_clicks_but_onboarding_does_not_auto_link(): void
    {
        Storage::fake('public');
        Notification::fake();

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
            ->get('/?ref=TESTCODE')
            ->assertOk();

        $this->assertSame(1, $affiliate->fresh()->click_count);

        $this->withCookie('omnireferral_affiliate', 'TESTCODE')
            ->post(route('onboarding.submit'), [
                'full_name' => 'Buyer Test',
                'email' => 'buyer@test.com',
                'phone' => '(555) 000-1234',
                'city' => 'Dallas',
                'state' => 'TX',
                'postal_code' => '75201',
                'terms' => true,
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->assertGuest();

        $this->assertDatabaseHas('users', [
            'email' => 'buyer@test.com',
            'referred_by_user_id' => null,
        ]);

        $this->assertDatabaseHas('affiliate_profiles', [
            'referral_code' => 'TESTCODE',
            'conversion_count' => 0,
        ]);
    }
}
