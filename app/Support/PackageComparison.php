<?php

namespace App\Support;

use App\Models\Package;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PackageComparison
{
    /**
     * Build comparison table rows from pricing content plans.
     *
     * @param  array<string, array<int, array<string, mixed>>>  $pricingPlans
     * @return array{headers: array<int, string>, rows: array<int, array<string, mixed>>}
     */
    public static function fromPricingPlans(array $pricingPlans): array
    {
        $plans = collect($pricingPlans['real_estate'] ?? [])->values();
        if ($plans->isEmpty()) {
            return ['headers' => [], 'rows' => []];
        }

        $headers = $plans->map(function (array $plan) {
            $name = (string) ($plan['name'] ?? 'Plan');
            $amount = (int) ($plan['price'] ?? 0);

            return $amount > 0 ? $name . ' ($' . number_format($amount) . ')' : $name;
        })->all();

        $rows = [
            ['type' => 'group', 'label' => 'Package snapshot'],
            [
                'feature' => 'Price',
                'values' => $plans->map(fn (array $plan) => '$' . number_format((int) ($plan['price'] ?? 0)))->all(),
            ],
            [
                'feature' => 'Billing',
                'values' => $plans->map(function (array $plan) {
                    $note = trim((string) ($plan['price_note'] ?? ''));
                    $note = trim(ltrim($note, '/ '));

                    return $note !== '' ? $note : '—';
                })->all(),
            ],
            [
                'feature' => 'Value price',
                'values' => $plans->map(fn (array $plan) => ! empty($plan['value_price']) ? '$' . number_format((int) $plan['value_price']) : '—')->all(),
            ],
            [
                'feature' => 'Referral fee',
                'values' => $plans->map(fn (array $plan) => self::extractReferralFee((array) ($plan['features'] ?? [])))->all(),
            ],
            [
                'feature' => 'Listing access',
                'values' => $plans->map(fn (array $plan) => self::extractListingAccess((array) ($plan['features'] ?? [])))->all(),
            ],
            [
                'feature' => 'Territory coverage',
                'values' => $plans->map(fn (array $plan) => self::extractTerritoryCoverage((array) ($plan['features'] ?? [])))->all(),
            ],
        ];

        $maxFeatures = (int) $plans->map(fn (array $plan) => count((array) ($plan['features'] ?? [])))->max();
        $maxFeatures = min($maxFeatures, 12);

        if ($maxFeatures > 0) {
            $rows[] = ['type' => 'group', 'label' => 'Included features (per package row)'];
            for ($i = 0; $i < $maxFeatures; $i++) {
                $label = $plans
                    ->map(fn (array $plan) => trim((string) (((array) ($plan['features'] ?? []))[$i] ?? '')))
                    ->first(fn (string $t) => $t !== '');

                if ($label === null || $label === '') {
                    $label = 'Plan highlight ' . ($i + 1);
                } else {
                    $label = Str::limit($label, 90);
                }

                $rows[] = [
                    'feature' => $label,
                    'values' => $plans->map(function (array $plan) use ($i) {
                        $feature = trim((string) (((array) ($plan['features'] ?? []))[$i] ?? ''));

                        return $feature !== '' ? 'yes' : 'no';
                    })->all(),
                ];
            }
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * Build comparison table rows from active lead packages in the database.
     *
     * @return array{headers: array<int, string>, rows: array<int, array<string, mixed>>}
     */
    public static function fromLeadPackages(Collection $packages): array
    {
        $packages = $packages->values();
        if ($packages->isEmpty()) {
            return ['headers' => [], 'rows' => []];
        }

        $headers = $packages->map(function (Package $p) {
            $amount = (int) ($p->one_time_price ?? $p->monthly_price ?? 0);

            return $p->name . ($amount > 0 ? ' ($' . number_format($amount) . ')' : '');
        })->all();

        $rows = [
            ['type' => 'group', 'label' => 'Package snapshot'],
            [
                'feature' => 'One-time price',
                'values' => $packages->map(fn (Package $p) => $p->one_time_price ? '$' . number_format((int) $p->one_time_price) : '—')->all(),
            ],
            [
                'feature' => 'Monthly price',
                'values' => $packages->map(fn (Package $p) => $p->monthly_price ? '$' . number_format((int) $p->monthly_price) . '/mo' : '—')->all(),
            ],
            [
                'feature' => 'Active listing slots',
                'values' => $packages->map(fn (Package $p) => (string) max(0, $p->listingLimit()))->all(),
            ],
            [
                'feature' => 'Billing type',
                'values' => $packages->map(fn (Package $p) => Str::headline((string) $p->billing_type))->all(),
            ],
        ];

        $maxFeatures = (int) $packages->map(fn (Package $p) => count($p->features ?? []))->max();
        $maxFeatures = min($maxFeatures, 12);

        if ($maxFeatures > 0) {
            $rows[] = ['type' => 'group', 'label' => 'Included features (per package row)'];
            for ($i = 0; $i < $maxFeatures; $i++) {
                $label = $packages
                    ->map(fn (Package $p) => trim((string) (($p->features ?? [])[$i] ?? '')))
                    ->first(fn (string $t) => $t !== '');

                if ($label === null || $label === '') {
                    $label = 'Plan highlight ' . ($i + 1);
                } else {
                    $label = Str::limit($label, 90);
                }

                $rows[] = [
                    'feature' => $label,
                    'values' => $packages->map(function (Package $p) use ($i) {
                        $line = trim((string) (($p->features ?? [])[$i] ?? ''));

                        return $line !== '' ? 'yes' : 'no';
                    })->all(),
                ];
            }
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    private static function extractReferralFee(array $features): string
    {
        foreach ($features as $feature) {
            $feature = (string) $feature;
            if (preg_match('/\\b(\\d{1,2})%\\s+referral\\s+fee\\b/i', $feature, $matches)) {
                return $matches[1] . '%';
            }
        }

        return '—';
    }

    private static function extractListingAccess(array $features): string
    {
        foreach ($features as $feature) {
            $feature = (string) $feature;
            if (stripos($feature, 'unlimited listings') !== false) {
                return 'Unlimited';
            }

            if (preg_match('/\\bList\\s+up\\s+to\\s+(\\d+)\\s+active\\s+listings\\b/i', $feature, $matches)) {
                return 'Up to ' . $matches[1];
            }
        }

        return '—';
    }

    private static function extractTerritoryCoverage(array $features): string
    {
        foreach ($features as $feature) {
            $feature = (string) $feature;
            if (preg_match('/\\bup\\s+to\\s+(\\d+)\\s+cities\\s+or\\s+ZIP\\s+codes\\b/i', $feature, $matches)) {
                return 'Up to ' . $matches[1] . ' cities/ZIPs';
            }
        }

        return '—';
    }
}
