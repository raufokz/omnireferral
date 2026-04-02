<?php

namespace Tests\Feature;

use App\Models\AffiliateProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
                 'password' => 'password123',
                 'password_confirmation' => 'password123',
                 'role' => 'buyer',
             ])
             ->assertRedirect(route('onboarding', 'buyer'));

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
