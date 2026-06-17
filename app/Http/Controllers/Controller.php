<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

abstract class Controller
{
    use AuthorizesRequests;

    protected function dashboardTrendWindows(string $period): Collection
    {
        $now = now();

        return match ($period) {
            'daily' => collect(range(6, 0))->map(function (int $daysAgo) use ($now) {
                $date = $now->copy()->subDays($daysAgo);

                return [
                    'label' => $date->format('D'),
                    'start' => $date->copy()->startOfDay(),
                    'end' => $date->copy()->endOfDay(),
                ];
            }),

            'weekly' => collect(range(7, 0))->map(function (int $weeksAgo) use ($now) {
                $date = $now->copy()->subWeeks($weeksAgo)->startOfWeek();

                return [
                    'label' => $date->format('M j'),
                    'start' => $date->copy()->startOfWeek(),
                    'end' => $date->copy()->endOfWeek(),
                ];
            }),

            'yearly' => collect(range(4, 0))->map(function (int $yearsAgo) use ($now) {
                $date = $now->copy()->subYears($yearsAgo);

                return [
                    'label' => $date->format('Y'),
                    'start' => $date->copy()->startOfYear(),
                    'end' => $date->copy()->endOfYear(),
                ];
            }),

            default => collect(range(5, 0))->map(function (int $monthsAgo) use ($now) {
                $date = $now->copy()->subMonths($monthsAgo)->startOfMonth();

                return [
                    'label' => $date->format('M'),
                    'start' => $date->copy()->startOfMonth(),
                    'end' => $date->copy()->endOfMonth(),
                ];
            }),
        };
    }

    protected function countTrendForQuery($query, string $period): Collection
    {
        $trend = $this->dashboardTrendWindows($period)->map(function (array $window) use ($query) {
            return [
                'label' => $window['label'],
                'count' => (clone $query)
                    ->whereBetween('created_at', [$window['start'], $window['end']])
                    ->count(),
            ];
        });

        return $this->withTrendPercent($trend, 'count');
    }

    protected function revenueTrendForQuery($query, array $revenueMap, string $period): Collection
    {
        $trend = $this->dashboardTrendWindows($period)->map(function (array $window) use ($query, $revenueMap) {
            $leads = (clone $query)
                ->whereBetween('created_at', [$window['start'], $window['end']])
                ->get(['package_type']);

            return [
                'label' => $window['label'],
                'amount' => (int) $leads->sum(function ($lead) use ($revenueMap) {
                    return $revenueMap[strtolower((string) $lead->package_type)] ?? 0;
                }),
            ];
        });

        return $this->withTrendPercent($trend, 'amount');
    }

    protected function withTrendPercent(Collection $trend, string $valueKey): Collection
    {
        $max = max(1, (int) $trend->max($valueKey));

        return $trend->map(function (array $row) use ($valueKey, $max) {
            return $row + [
                'percent' => (int) round(((int) ($row[$valueKey] ?? 0) / $max) * 100),
            ];
        });
    }

    protected function propertyTypeDistributionForQuery($query, int $take = 5): Collection
    {
        $rows = (clone $query)
            ->select('property_type', DB::raw('COUNT(*) as total'))
            ->groupBy('property_type')
            ->orderByDesc('total')
            ->take($take)
            ->get();

        $total = max(1, (int) $rows->sum('total'));

        return $rows->map(function ($row) use ($total) {
            return [
                'label' => $row->property_type ?: 'Other',
                'count' => (int) $row->total,
                'percent' => (int) round(((int) $row->total / $total) * 100),
            ];
        });
    }

    protected function pipelineHealthFromCounts(array $counts): Collection
    {
        $max = max(1, (int) collect($counts)->max('count'));

        return collect($counts)->map(function (array $stage) use ($max) {
            return $stage + [
                'percent' => (int) round(((int) ($stage['count'] ?? 0) / $max) * 100),
            ];
        });
    }

    protected function dashboardRevenueMap(): array
    {
        return [
            'starter' => 199,
            'growth' => 349,
            'elite' => 549,
            'quick' => 199,
            'power' => 349,
            'prime' => 549,
        ];
    }
}
