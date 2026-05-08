<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\GoHighLevelService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncUserToGoHighLevel implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /**
     * Backoff schedule in seconds.
     *
     * @var array<int>
     */
    public array $backoff = [60, 300, 900, 1800];

    public int $timeout = 20;

    public function __construct(public int $userId)
    {
    }

    public function handle(GoHighLevelService $service): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        try {
            $response = $service->syncUser($user);
        } catch (\Throwable $e) {
            Log::error('SyncUserToGoHighLevel job threw exception.', [
                'user_id' => $this->userId,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }

        if (! $response) {
            throw new \RuntimeException("GoHighLevel syncUser failed for user_id={$this->userId}");
        }
        $ghlId = data_get($response, 'contact.id') ?? data_get($response, 'id');

        if ($ghlId) {
            $user->forceFill([
                'ghl_contact_id' => $ghlId,
                'last_synced_at' => now(),
            ])->save();
        }
    }
}
