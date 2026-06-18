<?php

namespace App\Services;

use App\Models\GhlSetting;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoHighLevelService
{
    private ?GhlSetting $dbSettings = null;

    public function configured(): bool
    {
        return filled($this->resolveApiKey()) && filled($this->resolveLocationId());
    }

    /**
     * Test API connectivity. Returns ['ok' => bool, 'message' => string, 'status' => int|null].
     */
    public function testConnection(): array
    {
        if (! $this->configured()) {
            return ['ok' => false, 'message' => 'API key or Location ID not configured.', 'status' => null];
        }

        try {
            $response = Http::withToken($this->resolveApiKey())
                ->acceptJson()
                ->timeout(10)
                ->get(rtrim($this->resolveBaseUrl(), '/').'/contacts/', [
                    'locationId' => $this->resolveLocationId(),
                    'limit'      => 1,
                ]);

            if ($response->successful()) {
                $this->persistConnectionStatus('connected');

                return ['ok' => true, 'message' => 'Connection successful.', 'status' => $response->status()];
            }

            $this->persistConnectionStatus($response->status() === 401 ? 'invalid' : 'error');

            return [
                'ok'      => false,
                'message' => 'GHL returned HTTP '.$response->status().': '.mb_substr((string) $response->body(), 0, 300),
                'status'  => $response->status(),
            ];
        } catch (\Throwable $e) {
            $this->persistConnectionStatus('error');

            return ['ok' => false, 'message' => 'Exception: '.$e->getMessage(), 'status' => null];
        }
    }

    public function createOrUpdateContact(array $payload): ?array
    {
        if (! $this->configured()) {
            return null;
        }

        $response = Http::withToken($this->resolveApiKey())
            ->acceptJson()
            ->timeout($this->resolveHttpTimeout())
            ->retry($this->resolveHttpRetries(), $this->resolveHttpRetrySleepMs(), throw: false)
            ->post(rtrim($this->resolveBaseUrl(), '/').'/contacts/', [
                'locationId'   => $this->resolveLocationId(),
                'firstName'    => Arr::get($payload, 'first_name'),
                'lastName'     => Arr::get($payload, 'last_name'),
                'name'         => Arr::get($payload, 'name'),
                'email'        => Arr::get($payload, 'email'),
                'phone'        => Arr::get($payload, 'phone'),
                'source'       => Arr::get($payload, 'source', 'OmniReferral Platform'),
                'tags'         => Arr::wrap(Arr::get($payload, 'tags', [])),
                'customFields' => Arr::get($payload, 'custom_fields', []),
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::warning('GoHighLevel contact sync failed.', [
            'status' => $response->status(),
            'body'   => mb_substr((string) $response->body(), 0, 1500),
            'email'  => Arr::get($payload, 'email'),
            'source' => Arr::get($payload, 'source'),
        ]);

        return null;
    }

    public function syncLead(Lead $lead): ?array
    {
        return $this->createOrUpdateContact([
            'name'   => $lead->name,
            'email'  => $lead->email,
            'phone'  => $lead->phone,
            'source' => 'OmniReferral Lead Funnel',
            'tags'   => [
                strtoupper($lead->intent),
                strtoupper((string) $lead->package_type),
                strtoupper((string) $lead->status),
            ],
            'custom_fields' => [
                ['key' => 'zip_code',          'field_value' => $lead->zip_code],
                ['key' => 'property_address',  'field_value' => $lead->property_address],
                ['key' => 'timeline',          'field_value' => $lead->timeline],
                ['key' => 'financing_status',  'field_value' => $lead->financing_status],
                ['key' => 'lead_number',       'field_value' => $lead->lead_number],
            ],
        ]);
    }

    public function syncUser(User $user): ?array
    {
        $profile = $user->realtorProfile;

        return $this->createOrUpdateContact([
            'name'   => $user->name,
            'email'  => $user->email,
            'phone'  => $user->phone,
            'source' => 'OmniReferral User Provisioning',
            'tags'   => [$user->roleLabel()],
            'custom_fields' => [
                ['key' => 'role',           'field_value' => $user->role],
                ['key' => 'staff_team',     'field_value' => $user->staff_team],
                ['key' => 'affiliate_code', 'field_value' => $user->affiliate_code],
                ['key' => 'address_line_1', 'field_value' => $user->address_line_1],
                ['key' => 'address_line_2', 'field_value' => $user->address_line_2],
                ['key' => 'city',           'field_value' => $user->city],
                ['key' => 'state',          'field_value' => $user->state],
                ['key' => 'zip_code',       'field_value' => $user->zip_code],
                ['key' => 'brokerage_name', 'field_value' => $profile?->brokerage_name],
                ['key' => 'license_number', 'field_value' => $profile?->license_number],
            ],
        ]);
    }

    public function updateOpportunityStage(string $contactId, string $stage): bool
    {
        if (! $this->configured() || ! $contactId) {
            return false;
        }

        $response = Http::withToken($this->resolveApiKey())
            ->acceptJson()
            ->timeout($this->resolveHttpTimeout())
            ->retry($this->resolveHttpRetries(), $this->resolveHttpRetrySleepMs(), throw: false)
            ->put(rtrim($this->resolveBaseUrl(), '/').'/contacts/'.$contactId, [
                'locationId'    => $this->resolveLocationId(),
                'pipelineStage' => $stage,
            ]);

        if (! $response->successful()) {
            Log::warning('GoHighLevel opportunity stage update failed.', [
                'contact_id' => $contactId,
                'stage'      => $stage,
                'status'     => $response->status(),
                'body'       => mb_substr((string) $response->body(), 0, 1500),
            ]);
        }

        return $response->successful();
    }

    // ─── Settings resolution (DB first, then .env/config) ─────────────────────

    public function resolveApiKey(): ?string
    {
        $fromDb = $this->dbSetting()?->api_key;
        if (filled($fromDb)) {
            return $fromDb;
        }

        return config('services.gohighlevel.api_key') ?: null;
    }

    public function resolveLocationId(): ?string
    {
        $fromDb = $this->dbSetting()?->location_id;
        if (filled($fromDb)) {
            return $fromDb;
        }

        return config('services.gohighlevel.location_id') ?: null;
    }

    public function resolveBaseUrl(): string
    {
        return (string) config('services.gohighlevel.base_url', 'https://services.leadconnectorhq.com');
    }

    private function resolveHttpTimeout(): int
    {
        return (int) config('services.gohighlevel.http_timeout', 10);
    }

    private function resolveHttpRetries(): int
    {
        return (int) config('services.gohighlevel.http_retries', 3);
    }

    private function resolveHttpRetrySleepMs(): int
    {
        return (int) config('services.gohighlevel.http_retry_sleep_ms', 500);
    }

    private function dbSetting(): ?GhlSetting
    {
        if ($this->dbSettings === null) {
            $this->dbSettings = GhlSetting::first();
        }

        return $this->dbSettings;
    }

    private function persistConnectionStatus(string $status): void
    {
        try {
            $setting = GhlSetting::instance();
            $setting->connection_status = $status;
            $setting->last_tested_at = now();
            $setting->save();
        } catch (\Throwable) {
        }
    }
}
