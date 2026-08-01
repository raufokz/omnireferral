<?php

namespace Tests\Feature;

use App\Models\PricingPlan;
use Database\Seeders\PricingPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncProductionPlansCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_syncs_stale_pricing_rows_to_authoritative_content(): void
    {
        $this->seed(PricingPlanSeeder::class);

        PricingPlan::whereIn('slug', ['starter-leads', 'growth-leads', 'elite-leads'])
            ->update([
                'price' => 999,
                'price_note' => '/ Stale',
            ]);

        $this->artisan('plans:sync-production')
            ->expectsOutputToContain('starter-leads')
            ->expectsOutputToContain('growth-leads')
            ->expectsOutputToContain('elite-leads')
            ->assertExitCode(0);

        $this->assertSame(369, PricingPlan::where('slug', 'starter-leads')->value('price'));
        $this->assertSame(697, PricingPlan::where('slug', 'growth-leads')->value('price'));
        $this->assertSame(1979, PricingPlan::where('slug', 'elite-leads')->value('price'));
        $this->assertSame('/ Yearly', PricingPlan::where('slug', 'starter-leads')->value('price_note'));
        $this->assertSame('/ One-Time', PricingPlan::where('slug', 'growth-leads')->value('price_note'));
    }

    public function test_command_also_syncs_feature_content(): void
    {
        $this->seed(PricingPlanSeeder::class);

        PricingPlan::where('slug', 'starter-leads')->update([
            'features' => ['Stale Feature'],
            'summary' => 'Stale summary.',
            'cta_label' => 'STALE CTA',
        ]);

        $this->artisan('plans:sync-production')
            ->assertExitCode(0);

        $plan = PricingPlan::where('slug', 'starter-leads')->first();

        $this->assertSame('Starter-friendly. City-focused.', $plan->summary);
        $this->assertSame('GO STARTER', $plan->cta_label);
        $this->assertContains('1-2 Exclusive Referrals / Mo', $plan->features);
        $this->assertNotContains('Stale Feature', $plan->features);
    }

    public function test_dry_run_reports_changes_without_writing(): void
    {
        $this->seed(PricingPlanSeeder::class);

        PricingPlan::where('slug', 'starter-leads')->update(['price' => 999]);

        $this->artisan('plans:sync-production', ['--dry-run' => true])
            ->expectsOutputToContain('starter-leads')
            ->assertExitCode(0);

        $this->assertSame(999, PricingPlan::where('slug', 'starter-leads')->value('price'));
    }

    public function test_plan_already_in_sync_is_reported(): void
    {
        $this->seed(PricingPlanSeeder::class);

        $this->artisan('plans:sync-production')->assertExitCode(0);

        $this->artisan('plans:sync-production')
            ->expectsOutputToContain('in sync')
            ->assertExitCode(0);
    }
}
