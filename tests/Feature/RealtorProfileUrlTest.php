<?php

namespace Tests\Feature;

use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealtorProfileUrlTest extends TestCase
{
    use RefreshDatabase;

    private function makeSubscribedProfile(string $slug): RealtorProfile
    {
        $plan = \App\Models\Package::factory()->create();

        $user = User::factory()->create([
            'role' => 'agent',
            'status' => 'active',
            'current_plan_id' => $plan->id,
        ]);

        $profile = RealtorProfile::where('user_id', $user->id)->first();
        $profile->update([
            'slug' => $slug,
            'profile_status' => RealtorProfile::STATUS_APPROVED,
        ]);

        return $profile->fresh();
    }

    public function test_legacy_slug_url_redirects_permanently_to_realtor_suffixed_url(): void
    {
        $profile = $this->makeSubscribedProfile('daniel-foster');

        $response = $this->get('/realtors/daniel-foster');

        $response->assertRedirect('/realtors/daniel-foster-realtor');
        $response->assertStatus(301);
    }

    public function test_realtor_suffixed_url_renders_successfully(): void
    {
        $this->makeSubscribedProfile('kevin-a-carroll-sfr');

        $response = $this->get('/realtors/kevin-a-carroll-sfr-realtor');

        $response->assertStatus(200);
        $response->assertSee('/realtors/kevin-a-carroll-sfr-realtor', false);
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->get('/realtors/does-not-exist')->assertStatus(404);
        $this->get('/realtors/does-not-exist-realtor')->assertStatus(404);
    }
}
