<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class GoHighLevelService
{
    public function configured(): bool
    {
        return (bool) config('services.gohighlevel.api_key')
            && (bool) config('services.gohighlevel.location_id');
    }

    public function createOrUpdateContact(array $payload): ?array
    {
        if (! $this->configured()) {
            return null;
        }

        $response = Http::withToken(config('services.gohighlevel.api_key'))
            ->acceptJson()
            ->post(rtrim(config('services.gohighlevel.base_url'), '/') . '/contacts/', [
                'locationId' => config('services.gohighlevel.location_id'),
                'firstName' => Arr::get($payload, 'first_name'),
                'lastName' => Arr::get($payload, 'last_name'),
                'name' => Arr::get($payload, 'name'),
                'email' => Arr::get($payload, 'email'),
                'phone' => Arr::get($payload, 'phone'),
                'source' => Arr::get($payload, 'source', 'OmniReferral Platform'),
                'tags' => Arr::wrap(Arr::get($payload, 'tags', [])),
                'customFields' => Arr::get($payload, 'custom_fields', []),
            ]);

        return $response->successful() ? $response->json() : null;
    }

    public function syncLead(Lead $lead): ?array
    {
        return $this->createOrUpdateContact([
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'source' => 'OmniReferral Lead Funnel',
            'tags' => [
                strtoupper($lead->intent),
                strtoupper((string) $lead->package_type),
                strtoupper((string) $lead->status),
            ],
            'custom_fields' => [
                ['key' => 'zip_code', 'field_value' => $lead->zip_code],
                ['key' => 'timeline', 'field_value' => $lead->timeline],
                ['key' => 'financing_status', 'field_value' => $lead->financing_status],
                ['key' => 'lead_number', 'field_value' => $lead->lead_number],
            ],
        ]);
    }

    public function syncUser(User $user): ?array
    {
        $profile = $user->realtorProfile;

        return $this->createOrUpdateContact([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'source' => 'OmniReferral User Provisioning',
            'tags' => [$user->roleLabel()],
            'custom_fields' => [
                ['key' => 'role', 'field_value' => $user->role],
                ['key' => 'staff_team', 'field_value' => $user->staff_team],
                ['key' => 'affiliate_code', 'field_value' => $user->affiliate_code],
                ['key' => 'address_line_1', 'field_value' => $user->address_line_1],
                ['key' => 'address_line_2', 'field_value' => $user->address_line_2],
                ['key' => 'city', 'field_value' => $user->city],
                ['key' => 'state', 'field_value' => $user->state],
                ['key' => 'zip_code', 'field_value' => $user->zip_code],
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

        $response = Http::withToken(config('services.gohighlevel.api_key'))
            ->acceptJson()
            ->put(rtrim(config('services.gohighlevel.base_url'), '/') . '/contacts/' . $contactId, [
                'locationId' => config('services.gohighlevel.location_id'),
                'pipelineStage' => $stage,
            ]);

        return $response->successful();
    }
}
