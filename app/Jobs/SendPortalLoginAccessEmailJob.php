<?php

namespace App\Jobs;

use App\Mail\PortalLoginAccessMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPortalLoginAccessEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly int    $userId,
        public readonly string $plainPassword,
        public readonly string $loginUrl,
        public readonly string $dashboardUrl,
    ) {}

    public function handle(): void
    {
        $user = User::with('currentPlan')->find($this->userId);

        if (! $user) {
            Log::warning('SendPortalLoginAccessEmailJob: user not found.', ['user_id' => $this->userId]);

            return;
        }

        try {
            Mail::to($user->email)->send(new PortalLoginAccessMail(
                user: $user,
                temporaryPassword: $this->plainPassword,
                loginUrl: $this->loginUrl,
                dashboardUrl: $this->dashboardUrl,
            ));

            Log::info('Portal login access email sent.', ['user_id' => $user->id, 'email' => $user->email]);
        } catch (\Throwable $e) {
            Log::error('Failed to send portal login access email.', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendPortalLoginAccessEmailJob permanently failed.', [
            'user_id' => $this->userId,
            'error'   => $exception->getMessage(),
        ]);
    }
}
