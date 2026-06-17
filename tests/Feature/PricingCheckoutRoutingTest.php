<?php

namespace Tests\Feature;

use Database\Seeders\OmniReferralSeeder;
use Database\Seeders\PricingPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingCheckoutRoutingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, string>
     */
    private function pricingSlugs(): array
    {
        return [
            'starter-leads',
            'growth-leads',
            'elite-leads',
            'cold-calling-isa',
            'social-media-mgmt',
            'individual-va',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function publicPricingPageSlugs(): array
    {
        return $this->pricingSlugs();
    }

    public function test_pricing_page_links_displayed_plans_to_correct_destinations(): void
    {
        $this->seed(PricingPlanSeeder::class);

        $response = $this->get(route('pricing'));

        $response->assertOk();

        foreach ($this->publicPricingPageSlugs() as $slug) {
            $response->assertSee('/packages/'.$slug.'/checkout', false);
        }

        $response->assertSee('Perfect for agents entering new markets who need verified referrals, local visibility, and predictable lead opportunities.');
        $response->assertSee('Designed for growing teams that need more referrals, broader coverage, virtual assistance, and stronger lead qualification.');
        $response->assertSee('Dedicated virtual support for CRM updates, scheduling, administrative tasks, and daily business operations.');
        $response->assertDontSee(route('contact', ['plan' => 'Individual VA']), false);
        $response->assertDontSee('First FIVE (5) Referrals FREE');
        $response->assertDontSee('Dedicated Accounts Manager');
        $response->assertDontSee('pricing-card'.'__quick-list', false);
        $response->assertDontSee('stripe'.'-checkout', false);
    }

    public function test_all_seeded_pricing_checkout_routes_load(): void
    {
        $this->seed(OmniReferralSeeder::class);

        foreach ($this->pricingSlugs() as $slug) {
            $badge = match ($slug) {
                'starter-leads' => 'Starter',
                'growth-leads' => 'Growth',
                'elite-leads' => 'Elite',
                default => 'VA',
            };
            $guarantee = match ($slug) {
                'starter-leads' => 'Closing Guarantee Under 150 Days',
                'growth-leads' => 'Closing Guarantee Under 120 Days',
                'elite-leads' => 'Closing Guarantee Under 90 Days',
                'individual-va' => 'No Long-Term Commitment',
                default => null,
            };

            $response = $this->get(route('packages.checkout', ['packageSlug' => $slug]));
            $html = $response->getContent();

            $response
                ->assertOk()
                ->assertSee('Secure GoHighLevel Form')
                ->assertSee('<span class="pricing-label">'.$badge.'</span>', false)
                ->assertSee('Quick Highlights')
                ->assertSee('Complete Feature List')
                ->assertSee('What Happens After Submission')
                ->assertSee('Loading secure form')
                ->assertDontSee('Full Description')
                ->assertDontSee('Benefits')
                ->assertDontSee('Support Information')
                ->assertDontSee('Trust Indicators')
                ->assertDontSee('Preparing your secure OmniReferral form')
                ->assertDontSee('ghl-form-loader__skeleton', false)
                ->assertDontSee('package-detail-ribbon', false)
                ->assertDontSee('not configured here yet');

            $this->assertSame(1, substr_count($html, 'class="pricing-label"'));
            $this->assertSame(0, substr_count($html, 'pricing-badge-popular'));

            if ($guarantee !== null) {
                $this->assertSame(1, substr_count($html, $guarantee));
            }
        }
    }

    public function test_checkout_loads_from_pricing_plan_when_package_row_is_missing(): void
    {
        $this->seed(PricingPlanSeeder::class);

        $this->assertDatabaseMissing('packages', ['slug' => 'social-media-mgmt']);

        $this->get(route('packages.checkout', ['packageSlug' => 'social-media-mgmt']))
            ->assertOk()
            ->assertSee('Social Media Mgmt')
            ->assertSee('Quick Highlights')
            ->assertSee('Complete Feature List')
            ->assertSee('Daily Long + Short Form Videos')
            ->assertDontSee('Benefits')
            ->assertDontSee('Trust Indicators');

        $this->assertDatabaseMissing('packages', ['slug' => 'social-media-mgmt']);
    }

    public function test_checkout_page_uses_embedded_form_flow(): void
    {
        config(['services.stripe.secret' => '']);
        $this->seed(PricingPlanSeeder::class);

        $this->get(route('packages.checkout', ['packageSlug' => 'cold-calling-isa']))
            ->assertOk()
            ->assertSee('Secure GoHighLevel Form')
            ->assertDontSee('stripe'.'-checkout', false)
            ->assertDontSee('not configured here yet');

        $this->assertDatabaseMissing('packages', ['slug' => 'cold-calling-isa']);
    }
}
