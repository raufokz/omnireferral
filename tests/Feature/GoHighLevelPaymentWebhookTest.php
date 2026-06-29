<?php

namespace Tests\Feature;

use App\Models\AgentLeadQuota;
use App\Models\AgentSubscription;
use App\Models\Lead;
use App\Models\Package;
use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class GoHighLevelPaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    private Package $package;
    private string $webhookSecret = 'test-secret-key';

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();
        Notification::fake();

        Config::set('services.gohighlevel.webhook_secret', $this->webhookSecret);

        $this->package = Package::factory()->create([
            'slug' => 'starter-leads',
            'name' => 'Starter Lead Plan',
            'category' => 'lead',
            'is_active' => true,
            'monthly_lead_quota' => 5,
            'monthly_price' => 49900,
            'billing_type' => 'yearly',
        ]);
    }

    private function webhookHeaders(): array
    {
        return ['X-OmniReferral-Webhook' => $this->webhookSecret];
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'event_type' => 'onboarding_payment_completed',
            'ghl_contact_id' => 'ghl_contact_123',
            'full_name' => 'Test Agent',
            'email' => 'agent@example.com',
            'phone' => '555-123-4567',
            'license_number' => 'LIC12345',
            'brokerage_name' => 'Test Brokerage',
            'city' => 'Dallas',
            'state' => 'TX',
            'postal_code' => '75201',
            'package_slug' => 'starter-leads',
            'package_name' => 'Starter Lead Plan',
            'payment_status' => 'paid',
            'payment_amount' => '499.00',
            'payment_reference' => 'txn_ref_001',
            'headshot_url' => 'https://example.com/photo.jpg',
        ], $overrides);
    }

    public function test_ghl_onboarding_payment_creates_user(): void
    {
        $this->postJson(
            route('webhooks.gohighlevel.onboarding-payment'),
            $this->validPayload(),
            $this->webhookHeaders()
        )->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'agent@example.com',
            'name' => 'Test Agent',
            'role' => 'agent',
            'status' => 'active',
        ]);
    }

    public function test_ghl_onboarding_payment_creates_realtor_profile(): void
    {
        $this->postJson(
            route('webhooks.gohighlevel.onboarding-payment'),
            $this->validPayload(),
            $this->webhookHeaders()
        )->assertOk();

        $user = User::where('email', 'agent@example.com')->firstOrFail();

        $this->assertDatabaseHas('realtor_profiles', [
            'user_id' => $user->id,
            'brokerage_name' => 'Test Brokerage',
            'license_number' => 'LIC12345',
        ]);
    }

    public function test_ghl_onboarding_payment_creates_agent_subscription(): void
    {
        $this->postJson(
            route('webhooks.gohighlevel.onboarding-payment'),
            $this->validPayload(),
            $this->webhookHeaders()
        )->assertOk();

        $user = User::where('email', 'agent@example.com')->firstOrFail();

        $this->assertDatabaseHas('agent_subscriptions', [
            'user_id' => $user->id,
            'package_id' => $this->package->id,
            'payment_status' => 'paid',
            'payment_provider' => 'gohighlevel',
            'is_active' => true,
        ]);
    }

    public function test_ghl_onboarding_payment_creates_monthly_quota(): void
    {
        $this->postJson(
            route('webhooks.gohighlevel.onboarding-payment'),
            $this->validPayload(),
            $this->webhookHeaders()
        )->assertOk();

        $user = User::where('email', 'agent@example.com')->firstOrFail();
        $month = now()->format('Y-m');

        $this->assertDatabaseHas('agent_lead_quotas', [
            'user_id' => $user->id,
            'month' => $month,
            'monthly_quota' => 5,
        ]);
    }

    public function test_duplicate_webhook_does_not_duplicate_subscription(): void
    {
        $this->postJson(
            route('webhooks.gohighlevel.onboarding-payment'),
            $this->validPayload(),
            $this->webhookHeaders()
        )->assertOk();

        $this->postJson(
            route('webhooks.gohighlevel.onboarding-payment'),
            $this->validPayload(),
            $this->webhookHeaders()
        )->assertOk();

        $user = User::where('email', 'agent@example.com')->firstOrFail();

        $this->assertSame(1, AgentSubscription::where('user_id', $user->id)->count());
    }

    public function test_pending_payment_does_not_activate_subscription(): void
    {
        $this->postJson(
            route('webhooks.gohighlevel.onboarding-payment'),
            $this->validPayload(['payment_status' => 'pending']),
            $this->webhookHeaders()
        )->assertOk();

        $user = User::where('email', 'agent@example.com')->firstOrFail();

        $this->assertDatabaseHas('agent_subscriptions', [
            'user_id' => $user->id,
            'payment_status' => 'pending',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function test_missing_email_returns_422(): void
    {
        $this->postJson(
            route('webhooks.gohighlevel.onboarding-payment'),
            $this->validPayload(['email' => '']),
            $this->webhookHeaders()
        )->assertStatus(422);
    }

    public function test_invalid_webhook_secret_returns_401(): void
    {
        $this->postJson(
            route('webhooks.gohighlevel.onboarding-payment'),
            $this->validPayload(),
            ['X-OmniReferral-Webhook' => 'wrong-secret']
        )->assertStatus(401);
    }

    public function test_unmatched_package_does_not_crash(): void
    {
        $this->postJson(
            route('webhooks.gohighlevel.onboarding-payment'),
            $this->validPayload(['package_slug' => 'non-existent-package']),
            $this->webhookHeaders()
        )->assertOk();

        $this->assertDatabaseHas('users', ['email' => 'agent@example.com']);
    }

    public function test_existing_user_updated_on_webhook(): void
    {
        $user = User::factory()->create([
            'email' => 'agent@example.com',
            'name' => 'Old Name',
            'role' => 'agent',
            'status' => 'pending',
        ]);

        $this->postJson(
            route('webhooks.gohighlevel.onboarding-payment'),
            $this->validPayload(['full_name' => 'Updated Name']),
            $this->webhookHeaders()
        )->assertOk();

        $user->refresh();
        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('active', $user->status);
    }

    public function test_paid_payment_activates_user(): void
    {
        $this->postJson(
            route('webhooks.gohighlevel.onboarding-payment'),
            $this->validPayload(['payment_status' => 'completed']),
            $this->webhookHeaders()
        )->assertOk();

        $user = User::where('email', 'agent@example.com')->firstOrFail();
        $this->assertSame('active', $user->status);
    }
}
