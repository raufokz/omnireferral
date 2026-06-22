<?php

namespace App\Jobs;

use App\Mail\PortalAccessSetupMail;
use App\Models\OnboardingLog;
use App\Models\User;
use App\Services\PasswordSetupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Generates a one-time password-setup token and emails the secure setup link.
 * No plaintext password is ever generated or transmitted.
 */
class SendPortalAccessSetupEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly int     $userId,
        public readonly ?int    $onboardingLogId = null,
        public readonly string  $via = 'ghl_onboarding',
    ) {}

    public function handle(PasswordSetupService $setupService): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            Log::warning('SendPortalAccessSetupEmailJob: user not found.', ['user_id' => $this->userId]);

            return;
        }

        if (blank($user->email)) {
            Log::warning('SendPortalAccessSetupEmailJob: user has no email.', ['user_id' => $user->id]);
            $this->markLog('failed', 'Email missing from user record.');

            return;
        }

        try {
            $plain = $setupService->generate($user, $this->via);
            $url   = $setupService->url($plain);

            if (blank($url)) {
                $this->markLog('failed', 'Password setup URL is empty — token generation failed.', tokenGenerated: false);

                Log::error('SendPortalAccessSetupEmailJob: password setup URL is empty.', [
                    'user_id' => $user->id,
                    'email'   => $user->email,
                ]);

                return;
            }

            Mail::to($user->email)->send(new PortalAccessSetupMail(
                user: $user,
                setupUrl: $url,
                loginUrl: route('login'),
            ));

            $this->markLog('sent', tokenGenerated: true);

            Log::info('Portal access setup email sent.', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'via'     => $this->via,
            ]);
        } catch (\Throwable $e) {
            $this->markLog('failed', $e->getMessage(), tokenGenerated: false);

            Log::error('Failed to send portal access setup email.', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->markLog('failed', $exception->getMessage());

        Log::error('SendPortalAccessSetupEmailJob permanently failed.', [
            'user_id' => $this->userId,
            'error'   => $exception->getMessage(),
        ]);
    }

    private function markLog(string $status, ?string $error = null, ?bool $tokenGenerated = null): void
    {
        if (! $this->onboardingLogId) {
            return;
        }

        $log = OnboardingLog::find($this->onboardingLogId);
        if (! $log) {
            return;
        }

        $log->email_status   = $status;
        $log->email_sent     = $status === 'sent';
        $log->email_sent_at  = $status === 'sent' ? now() : $log->email_sent_at;
        $log->error_message  = $error ? Str::limit($error, 1000) : ($status === 'sent' ? null : $log->error_message);

        if ($tokenGenerated !== null) {
            $log->token_generated = $tokenGenerated;
            if ($tokenGenerated) {
                $log->token_expires_at = now()->addHours(PasswordSetupService::TTL_HOURS);
            }
        }

        $log->save();
    }
}
