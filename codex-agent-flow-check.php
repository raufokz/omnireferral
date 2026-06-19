<?php

use App\Http\Controllers\RealtorController;
use App\Models\RealtorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function checkAgentSubmission(array $payload, bool $expectedActive): string
{
    DB::beginTransaction();

    try {
        app(RealtorController::class)->submitAgentProfile(Request::create('/agents', 'POST', $payload));

        $profile = RealtorProfile::query()
            ->whereHas('user', fn ($query) => $query->where('name', $payload['name']))
            ->first();

        $ok = $profile
            && $profile->profile_status === RealtorProfile::STATUS_PUBLISHED
            && (bool) $profile->is_active_agent === $expectedActive
            && $profile->submission_source === 'public_agents_page'
            && $profile->isPublicVisible();

        return $ok ? 'ok' : 'failed';
    } finally {
        DB::rollBack();
    }
}

$base = [
    'role' => 'agent',
    'agent_directory_submission' => '1',
    'phone' => '555-010-'.random_int(1000, 9999),
    'brokerage_name' => 'Codex Realty',
    'city' => 'Dallas',
    'state' => 'TX',
    'terms_accepted' => '1',
    'communication_accepted' => '1',
];

echo checkAgentSubmission($base + [
    'name' => 'Codex Active Agent',
    'email' => '',
    'is_active_agent' => '1',
], true);

echo PHP_EOL;

echo checkAgentSubmission($base + [
    'name' => 'Codex Inactive Agent',
    'email' => 'codex-agent-'.random_int(1000, 9999).'@example.test',
    'is_active_agent' => '0',
], false);

echo PHP_EOL;
