<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The live public pricing page reads marketing copy from `pricing_plans`
     * (via App\Support\PricingContent::loadFromDatabase()) — a separate table
     * from `packages`, which was fixed for functional listing limits in
     * 2026_08_08_000001. This migration brings the marketing copy in line:
     * Starter Lead had no listing bullet at all, and Elite Lead said "5
     * Listings" when its actual capability limit is 10.
     */
    public function up(): void
    {
        if (! Schema::hasTable('pricing_plans')) {
            return;
        }

        $updates = [
            'starter-leads' => [
                'insert_after_index' => 4, // after "Select up to 2 Cities or ZIP Codes"
                'bullet' => 'Up to 2 Active Property Listings / Month',
            ],
            'elite-leads' => [
                'replace' => ['Showcase 5 Listings on Website' => 'Showcase 10 Listings on Website'],
            ],
        ];

        foreach ($updates as $slug => $change) {
            $row = DB::table('pricing_plans')->where('slug', $slug)->first();
            if (! $row) {
                continue;
            }

            $features = json_decode((string) $row->features, true) ?: [];

            if (isset($change['bullet'])) {
                $hasBullet = collect($features)->contains(fn ($f) => str_contains(strtolower((string) $f), 'active property listing'));
                if (! $hasBullet) {
                    array_splice($features, min($change['insert_after_index'], count($features)), 0, [$change['bullet']]);
                }
            }

            if (isset($change['replace'])) {
                foreach ($change['replace'] as $from => $to) {
                    $features = array_map(fn ($f) => $f === $from ? $to : $f, $features);
                }
            }

            DB::table('pricing_plans')->where('id', $row->id)->update(['features' => json_encode($features)]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('pricing_plans')) {
            return;
        }

        $starter = DB::table('pricing_plans')->where('slug', 'starter-leads')->first();
        if ($starter) {
            $features = array_values(array_filter(
                json_decode((string) $starter->features, true) ?: [],
                fn ($f) => ! str_contains(strtolower((string) $f), 'active property listing')
            ));
            DB::table('pricing_plans')->where('id', $starter->id)->update(['features' => json_encode($features)]);
        }

        $elite = DB::table('pricing_plans')->where('slug', 'elite-leads')->first();
        if ($elite) {
            $features = json_decode((string) $elite->features, true) ?: [];
            $features = array_map(fn ($f) => $f === 'Showcase 10 Listings on Website' ? 'Showcase 5 Listings on Website' : $f, $features);
            DB::table('pricing_plans')->where('id', $elite->id)->update(['features' => json_encode($features)]);
        }
    }
};
