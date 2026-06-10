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

    public function test_pricing_page_links_every_displayed_plan_to_checkout(): void
    {
        $this->seed(PricingPlanSeeder::class);

        $response = $this->get(route('pricing'));

        $response->assertOk();
        foreach ($this->pricingSlugs() as $slug) {
            $response->assertSee('/packages/'.$slug.'/checkout', false);
        }
    }

    public function test_all_seeded_pricing_checkout_routes_load(): void
    {
        $this->seed(OmniReferralSeeder::class);

        foreach ($this->pricingSlugs() as $slug) {
            $this->get(route('packages.checkout', ['packageSlug' => $slug]))
                ->assertOk()
                ->assertSee('Checkout Options');
        }
    }

    public function test_checkout_loads_from_pricing_plan_when_package_row_is_missing(): void
    {
        $this->seed(PricingPlanSeeder::class);

        $this->assertDatabaseMissing('packages', ['slug' => 'social-media-mgmt']);

        $this->get(route('packages.checkout', ['packageSlug' => 'social-media-mgmt']))
            ->assertOk()
            ->assertSee('Social Media Mgmt')
            ->assertSee('What you get')
            ->assertSee('Turn your real estate brand into a daily content engine');

        $this->assertDatabaseMissing('packages', ['slug' => 'social-media-mgmt']);
    }

    public function test_checkout_start_persists_missing_package_before_payment_flow(): void
    {
        config(['services.stripe.secret' => '']);
        $this->seed(PricingPlanSeeder::class);

        $checkoutUrl = route('packages.checkout', ['packageSlug' => 'cold-calling-isa']);

        $this->from($checkoutUrl)
            ->post(route('packages.checkout.start', ['packageSlug' => 'cold-calling-isa']), [
                'billing' => 'monthly',
            ])
            ->assertRedirect($checkoutUrl)
            ->assertSessionHas('error');

        $this->assertDatabaseHas('packages', [
            'slug' => 'cold-calling-isa',
            'category' => 'virtual_assistant',
            'billing_type' => 'monthly',
            'monthly_price' => 1999,
        ]);
    }
}
