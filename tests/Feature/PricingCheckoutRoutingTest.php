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
            'quick-leads',
            'power-leads',
            'prime-leads',
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

        foreach (['quick-leads', 'power-leads', 'prime-leads', 'cold-calling-isa', 'social-media-mgmt'] as $slug) {
            $response->assertSee('/packages/'.$slug.'/checkout', false);
        }

        $response->assertSee(route('contact'), false);
        $response->assertDontSee('/packages/individual-va/checkout', false);
        $response->assertDontSee('pricing-card'.'__quick-list', false);
        $response->assertDontSee('stripe'.'-checkout', false);
    }

    public function test_all_seeded_pricing_checkout_routes_load(): void
    {
        $this->seed(OmniReferralSeeder::class);

        foreach ($this->pricingSlugs() as $slug) {
            $this->get(route('packages.checkout', ['packageSlug' => $slug]))
                ->assertOk()
                ->assertSee('Secure GoHighLevel Form')
                ->assertSee('Loading secure form...')
                ->assertDontSee('not configured here yet');
        }
    }

    public function test_checkout_loads_from_pricing_plan_when_package_row_is_missing(): void
    {
        $this->seed(PricingPlanSeeder::class);

        $this->assertDatabaseMissing('packages', ['slug' => 'social-media-mgmt']);

        $this->get(route('packages.checkout', ['packageSlug' => 'social-media-mgmt']))
            ->assertOk()
            ->assertSee('Social Media Mgmt')
            ->assertSee('What Is Included')
            ->assertSee('Consistent visibility and audience growth');

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
