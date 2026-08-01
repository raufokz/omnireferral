<?php

namespace App\Console\Commands;

use App\Models\PricingPlan;
use App\Support\PricingContent;
use Illuminate\Console\Command;

class SyncProductionPlansCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plans:sync-production {--dry-run : Report planned changes without writing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs pricing_plans rows with the authoritative plan content shown on the pricing page';

    /**
     * @var array<int, string>
     */
    private const SYNCED_FIELDS = [
        'price',
        'price_note',
        'summary',
        'features',
        'cta_label',
        'is_featured',
    ];

    public function handle(): int
    {
        $plans = PricingPlan::orderBy('sort_order')->get();

        if ($plans->isEmpty()) {
            $this->warn('No pricing_plans rows found. Run `php artisan db:seed --class=PricingPlanSeeder` first.');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;

        foreach ($plans as $plan) {
            $enriched = PricingContent::planBySlug($plan->slug);

            if ($enriched === null) {
                $this->line(sprintf('  <comment>skip</comment> %s (no authoritative content)', $plan->slug));
                continue;
            }

            $updates = [];

            foreach (self::SYNCED_FIELDS as $field) {
                if (! array_key_exists($field, $enriched)) {
                    continue;
                }

                $target = $enriched[$field];
                $current = $plan->getAttribute($field);

                if (is_array($target) || is_array($current)) {
                    if ($target != $current) {
                        $updates[$field] = $target;
                    }
                } elseif ((string) $current !== (string) $target) {
                    $updates[$field] = $target;
                }
            }

            if ($updates === []) {
                $this->line(sprintf('  <info>ok</info> %s (in sync)', $plan->slug));
                continue;
            }

            $updated++;

            $this->line(sprintf('  <info>update</info> %s', $plan->slug));

            foreach ($updates as $field => $value) {
                $pretty = is_array($value) ? '['.count($value).' items]' : $value;
                $this->line(sprintf('    - %s: %s', $field, $pretty));
            }

            if (! $dryRun) {
                $plan->update($updates);
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->info(sprintf('[dry-run] %d plan(s) would be updated.', $updated));
        } else {
            $this->info(sprintf('Synced %d plan(s).', $updated));
        }

        return self::SUCCESS;
    }
}
