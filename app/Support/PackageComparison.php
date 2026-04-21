<?php

namespace App\Support;

use App\Models\Package;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PackageComparison
{
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
}
