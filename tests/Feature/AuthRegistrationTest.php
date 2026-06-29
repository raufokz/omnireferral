<?php

namespace Tests\Feature;

use App\Jobs\SyncUserToGoHighLevel;
use App\Models\RealtorProfile;
use App\Models\User;
use App\Notifications\AgentCredentialsNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
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

    public function test_auth_pages_render_the_workspace_selector(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Select your workspace')
            ->assertSee('Select Workspace')
            ->assertSee('Staff')
            ->assertSee('<select id="login-workspace"', false)
            ->assertDontSee('workspace-option');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Choose a user type')
            ->assertSee('Select Workspace')
            ->assertSee('Seller')
            ->assertSee('<select id="register-workspace"', false)
            ->assertDontSee('workspace-option');
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
            'terms_accepted' => true,
            'communication_accepted' => true,
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->assertGuest();

        $user = User::where('email', 'taylor@example.com')->firstOrFail();

        $this->assertSame('pending', $user->status);
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
        $this->assertSame('Dallas', $profile->service_city);
        $this->assertSame('TX', $profile->service_state);
        $this->assertSame('75201', $profile->service_zip_code);

        Queue::assertPushed(SyncUserToGoHighLevel::class);
    }

    public function test_public_agent_directory_submission_does_not_require_password(): void
    {
        Storage::fake('public');
        Queue::fake();

        $this->post(route('register'), [
            'role' => 'agent',
            'agent_directory_submission' => '1',
            'name' => 'Morgan Reyes',
            'email' => 'morgan-agent@example.com',
            'phone' => '(555) 777-1212',
            'address_line_1' => '45 Referral Avenue',
            'city' => 'Austin',
            'state' => 'tx',
            'zip_code' => '73301',
            'brokerage_name' => 'Omni Realty Partners',
            'license_number' => 'TX-7654321',
            'profile_image' => $this->fakePngUpload('directory-agent.png'),
            'terms_accepted' => true,
            'communication_accepted' => true,
        ])
            ->assertRedirect(route('agents.index'))
            ->assertSessionHas('success');

        $user = User::where('email', 'morgan-agent@example.com')->firstOrFail();

        $this->assertSame('agent', $user->role);
        $this->assertSame('pending', $user->status);
        $this->assertTrue((bool) $user->must_reset_password);
        $this->assertNotEmpty($user->password);

        $profile = $user->realtorProfile()->first();

        $this->assertNotNull($profile);
        $this->assertSame('Omni Realty Partners', $profile->brokerage_name);
        $this->assertSame('TX-7654321', $profile->license_number);

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
            'terms_accepted' => true,
            'communication_accepted' => true,
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('selected_workspace', 'buyer')
            ->assertSessionHas('success');

        $this->assertGuest();

        $user = User::where('email', 'jamie@example.com')->firstOrFail();

        $this->assertSame('pending', $user->status);
        $this->assertSame('buyer', $user->role);
        $this->assertNull($user->realtorProfile);
        Queue::assertPushed(SyncUserToGoHighLevel::class);
    }

    public function test_workspace_selection_is_required_for_login_and_registration(): void
    {
        Storage::fake('public');

        $this->post(route('login'), [
            'email' => 'missing-workspace@example.com',
            'password' => 'super-secret-password',
        ])->assertSessionHasErrors('role');

        $this->post(route('register'), [
            'name' => 'Workspace Missing',
            'email' => 'workspace-missing@example.com',
            'phone' => '(555) 222-3333',
            'address_line_1' => '100 Main Street',
            'city' => 'Dallas',
            'state' => 'TX',
            'zip_code' => '75201',
            'password' => 'super-secret-password',
            'password_confirmation' => 'super-secret-password',
            'profile_image' => $this->fakePngUpload('workspace-missing.png'),
            'terms_accepted' => true,
            'communication_accepted' => true,
        ])->assertSessionHasErrors('role');
    }

    public function test_login_workspace_selection_is_remembered_during_the_session(): void
    {
        $this->post(route('login'), [
            'role' => 'staff',
            'email' => 'not-found@example.com',
            'password' => 'super-secret-password',
        ])
            ->assertSessionHas('selected_workspace', 'staff')
            ->assertSessionHasErrors('email');
    }

    public function test_pending_user_cannot_sign_in_until_an_admin_activates_the_account(): void
    {
        Storage::fake('public');
        Queue::fake();

        $this->post(route('register'), [
            'role' => 'buyer',
            'name' => 'Pending Pat',
            'email' => 'pending-pat@example.com',
            'phone' => '(555) 999-0000',
            'address_line_1' => '9 Wait Street',
            'city' => 'Austin',
            'state' => 'TX',
            'zip_code' => '73301',
            'password' => 'super-secret-password',
            'password_confirmation' => 'super-secret-password',
            'profile_image' => $this->fakePngUpload('pat.png'),
            'terms_accepted' => true,
            'communication_accepted' => true,
        ])->assertRedirect(route('login'));

        $user = User::where('email', 'pending-pat@example.com')->firstOrFail();
        $this->assertSame('pending', $user->status);

        $this->post(route('login'), [
            'role' => 'buyer',
            'email' => 'pending-pat@example.com',
            'password' => 'super-secret-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->post(route('admin.users.review', $user), [
            'decision' => 'approve',
        ])->assertSessionHas('success');

        $this->assertSame('active', $user->fresh()->status);

        $this->post(route('login'), [
            'role' => 'buyer',
            'email' => 'pending-pat@example.com',
            'password' => 'super-secret-password',
        ])->assertRedirect(route('dashboard.buyer'));

        $this->assertAuthenticated();
    }

    public function test_legacy_onboarding_routes_redirect_to_login_for_guests(): void
    {
        $this->get(route('onboarding', 'agent'))
            ->assertRedirect(route('login'));

        $this->get(route('client.form.submission', ['role' => 'agent']))
            ->assertOk();
    }

    public function test_onboarding_form_renders(): void
    {
        $this->get(route('onboarding.form'))
            ->assertOk()
            ->assertSee('Complete Your Onboarding')
            ->assertSee('full_name')
            ->assertSee('email')
            ->assertSee('upload_picture');
    }

    public function test_onboarding_creates_user_and_realtor_profile(): void
    {
        Storage::fake('public');
        Notification::fake();

        $this->post(route('onboarding.submit'), [
            'full_name' => 'Alex Morgan',
            'phone' => '(555) 111-2222',
            'email' => 'alex@example.com',
            'license_number' => 'TX-1234567',
            'brokerage_name' => 'Premier Realty Group',
            'city' => 'Dallas',
            'state' => 'TX',
            'postal_code' => '75201',
            'primary_area_of_service' => 'Dallas-Fort Worth',
            'radius_miles' => '50',
            'secondary_area' => 'Fort Worth',
            'lead_types' => 'Buyer Representation, Seller Strategy',
            'languages' => 'English, Spanish',
            'upload_picture' => $this->fakePngUpload('headshot.png'),
            'terms' => true,
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $user = User::where('email', 'alex@example.com')->firstOrFail();

        $this->assertSame('Alex Morgan', $user->name);
        $this->assertSame('agent', $user->role);
        $this->assertSame('pending', $user->status);
        $this->assertSame('Dallas', $user->city);
        $this->assertSame('TX', $user->state);
        $this->assertSame('75201', $user->zip_code);
        $this->assertNotNull($user->onboarding_completed_at);
        $this->assertTrue((bool) $user->must_reset_password);
        $this->assertNull($user->password_set_at);
        $this->assertNotNull($user->avatar);
        $this->assertNotEmpty($user->password);

        Storage::disk('public')->assertExists($user->avatar);

        $profile = $user->realtorProfile()->first();

        $this->assertNotNull($profile);
        $this->assertSame('Dallas', $profile->service_city);
        $this->assertSame('TX', $profile->service_state);
        $this->assertSame('75201', $profile->service_zip_code);
        $this->assertSame('Premier Realty Group', $profile->brokerage_name);
        $this->assertSame('TX-1234567', $profile->license_number);
        $this->assertSame(2, $profile->years_of_experience);
        $this->assertSame('English, Spanish', $profile->languages);
        $this->assertSame('Buyer Representation, Seller Strategy', $profile->specialties);
        $this->assertSame('draft', $profile->profile_status);
        $this->assertTrue($profile->is_active_agent);
        $this->assertSame('onboarding_form', $profile->submission_source);
        $this->assertNull($profile->approved_at);
        $this->assertNotNull($profile->headshot);

        Storage::disk('public')->assertExists($profile->headshot);

        Notification::assertSentTo($user, AgentCredentialsNotification::class);
    }

    public function test_onboarding_requires_terms_acceptance(): void
    {
        $this->post(route('onboarding.submit'), [
            'full_name' => 'No Terms',
            'phone' => '(555) 000-0000',
            'email' => 'noterms@example.com',
            'city' => 'Austin',
            'state' => 'TX',
            'postal_code' => '73301',
            'terms' => false,
        ])->assertSessionHasErrors('terms');
    }

    public function test_onboarding_requires_required_fields(): void
    {
        $this->post(route('onboarding.submit'), [])
            ->assertSessionHasErrors(['full_name', 'phone', 'email', 'city', 'state', 'postal_code', 'terms']);
    }

    public function test_onboarding_uses_transaction_and_rolls_back_on_failure(): void
    {
        Storage::fake('public');
        Notification::fake();

        $this->post(route('onboarding.submit'), [
            'full_name' => 'Rollback Test',
            'phone' => '(555) 999-9999',
            'email' => 'rollback-' . uniqid() . '@example.com',
            'city' => 'Houston',
            'state' => 'TX',
            'postal_code' => '77001',
            'terms' => true,
        ])->assertRedirect(route('login'));

        $this->assertCount(1, User::where('email', 'like', 'rollback-%@example.com')->get());
    }

    public function test_must_reset_password_redirects_to_password_change(): void
    {
        $user = User::withoutEvents(fn () => User::factory()->create([
            'email' => 'must-reset@example.com',
            'password' => bcrypt('password'),
            'must_reset_password' => true,
            'status' => 'active',
            'role' => 'agent',
        ]));

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('password.change'))
            ->assertSessionHas('info');

        $this->actingAs($user)
            ->get(route('password.change'))
            ->assertOk();
    }

    public function test_password_change_page_requires_auth(): void
    {
        $this->get(route('password.change'))
            ->assertRedirect(route('login'));
    }

    public function test_password_change_updates_password_and_clears_flag(): void
    {
        $user = User::withoutEvents(fn () => User::factory()->create([
            'email' => 'pw-change@example.com',
            'password' => bcrypt('old-password'),
            'must_reset_password' => true,
            'password_set_at' => null,
            'status' => 'active',
            'role' => 'agent',
        ]));

        $this->actingAs($user);

        $this->post(route('password.change.update'), [
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect($user->dashboardRoute());

        $user->refresh();

        $this->assertFalse((bool) $user->must_reset_password);
        $this->assertNotNull($user->password_set_at);
        $this->assertTrue($user->passwordMatches('new-secure-password'));
    }

    public function test_password_change_requires_current_password_when_set(): void
    {
        $user = User::withoutEvents(fn () => User::factory()->create([
            'email' => 'pw-has-set@example.com',
            'password' => bcrypt('existing-pass'),
            'must_reset_password' => false,
            'password_set_at' => now()->subDay(),
            'status' => 'active',
            'role' => 'agent',
        ]));

        $this->actingAs($user);

        $this->post(route('password.change.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('current_password');

        $this->post(route('password.change.update'), [
            'current_password' => 'existing-pass',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect($user->dashboardRoute());

        $user->refresh();
        $this->assertTrue($user->passwordMatches('new-password'));
    }

    public function test_onboarding_welcome_email_sent(): void
    {
        Notification::fake();
        Storage::fake('public');

        $this->post(route('onboarding.submit'), [
            'full_name' => 'Email Test',
            'phone' => '(555) 111-2222',
            'email' => 'emailtest@example.com',
            'city' => 'Dallas',
            'state' => 'TX',
            'postal_code' => '75201',
            'terms' => true,
        ])->assertRedirect(route('login'));

        $user = User::where('email', 'emailtest@example.com')->firstOrFail();

        Notification::assertSentTo($user, AgentCredentialsNotification::class);
    }

    public function test_forgot_password_sends_reset_link(): void
    {
        Notification::fake();

        $user = User::withoutEvents(fn () => User::factory()->create([
            'email' => 'forgot-test@example.com',
            'password' => bcrypt('password'),
            'status' => 'active',
            'role' => 'agent',
        ]));

        $this->post(route('password.email'), [
            'email' => 'forgot-test@example.com',
        ])->assertSessionHas('success');

        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\ResetPassword::class);
    }

    public function test_reset_password_clears_must_reset_password_flag(): void
    {
        $user = User::factory()->create([
            'email' => 'reset-clears@example.com',
            'password' => bcrypt('old-password'),
            'must_reset_password' => true,
            'password_set_at' => null,
            'status' => 'active',
            'role' => 'buyer',
        ]);

        $token = \Illuminate\Support\Facades\Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'reset-clears@example.com',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect(route('login'));

        $user->refresh();

        $this->assertFalse((bool) $user->must_reset_password);
        $this->assertNotNull($user->password_set_at);
        $this->assertTrue($user->passwordMatches('new-secure-password'));
    }
}
