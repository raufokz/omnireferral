<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\GoHighLevelService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncUserToGoHighLevel implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $userId)
    {
    }

    public function handle(GoHighLevelService $service): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        $response = $service->syncUser($user);
        $ghlId = data_get($response, 'contact.id') ?? data_get($response, 'id');

        if ($ghlId) {
            $user->forceFill([
                'ghl_contact_id' => $ghlId,
                'last_synced_at' => now(),
            ])->save();
        }
    }
}
