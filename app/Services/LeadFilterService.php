<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LeadFilterService
{
    public function normalizeFromRequest(Request $request): array
    {
        return [
            'search' => trim((string) $request->string('search')->value()),
            'intent' => trim((string) $request->string('intent')->value()),
            'status' => trim((string) $request->string('status')->value()),
            'agent_id' => $request->integer('agent_id') ?: null,
            'rep_name' => trim((string) $request->string('rep_name')->value()),
            'source' => trim((string) $request->string('source')->value()),
            'date_from' => $this->normalizeFilterDate((string) $request->string('date_from')->value()),
            'date_to' => $this->normalizeFilterDate((string) $request->string('date_to')->value()),
        ];
    }

    public function normalizeFromArray(array $input): array
    {
        $value = fn (string $key) => is_string($input[$key] ?? null) ? trim((string) $input[$key]) : '';

        return [
            'search' => $value('search'),
            'intent' => $value('intent'),
            'status' => $value('status'),
            'agent_id' => isset($input['agent_id']) && is_numeric($input['agent_id']) ? (int) $input['agent_id'] : null,
            'rep_name' => $value('rep_name'),
            'source' => $value('source'),
            'date_from' => $this->normalizeFilterDate($value('date_from')),
            'date_to' => $this->normalizeFilterDate($value('date_to')),
        ];
    }

    public function apply($query, array $filters): void
    {
        $query
            ->when($filters['search'], function ($builder, string $search) {
                $builder->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('lead_number', 'like', "%{$search}%")
                        ->orWhere('zip_code', 'like', "%{$search}%")
                        ->orWhere('property_address', 'like', "%{$search}%")
                        ->orWhere('rep_name', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%")
                        ->orWhere('sent_to', 'like', "%{$search}%")
                        ->orWhere('source', 'like', "%{$search}%");
                });
            })
            ->when($filters['intent'], fn ($builder, string $intent) => $builder->where('intent', $intent))
            ->when($filters['status'], fn ($builder, string $status) => $builder->where('status', $status))
            ->when($filters['agent_id'], fn ($builder, int $agentId) => $builder->where('assigned_agent_id', $agentId))
            ->when($filters['rep_name'], fn ($builder, string $repName) => $builder->where('rep_name', $repName))
            ->when($filters['source'], fn ($builder, string $source) => $builder->where('source', $source))
            ->when($filters['date_from'] || $filters['date_to'], function ($builder) use ($filters) {
                $from = $filters['date_from'] ? Carbon::parse($filters['date_from'])->startOfDay() : null;
                $to = $filters['date_to'] ? Carbon::parse($filters['date_to'])->endOfDay() : null;

                $builder->where(function ($q) use ($from, $to) {
                    $q->where(function ($inner) use ($from, $to) {
                        $inner->whereNotNull('source_timestamp');
                        if ($from && $to) {
                            $inner->whereBetween('source_timestamp', [$from, $to]);
                        } elseif ($from) {
                            $inner->where('source_timestamp', '>=', $from);
                        } elseif ($to) {
                            $inner->where('source_timestamp', '<=', $to);
                        }
                    })->orWhere(function ($inner) use ($from, $to) {
                        $inner->whereNull('source_timestamp');
                        if ($from && $to) {
                            $inner->whereBetween('created_at', [$from, $to]);
                        } elseif ($from) {
                            $inner->where('created_at', '>=', $from);
                        } elseif ($to) {
                            $inner->where('created_at', '<=', $to);
                        }
                    });
                });
            });
    }

    private function normalizeFilterDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}

