<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthLog;
use App\Models\EmailLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use OpenApi\Attributes as OA;

class EmailToolsController extends Controller
{
    #[OA\Get(
        path: '/admin/email',
        tags: ['Admin', 'Email System'],
        summary: 'View email and auth diagnostics',
        description: 'Shows current mail configuration, delivery stats, email logs, and auth activity. Admin only.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Email diagnostics page'),
            new OA\Response(response: 403, description: 'Forbidden - admin access required'),
        ]
    )]
    public function index(): View
    {
        $driver       = config('mail.default');
        $mailerConfig = config("mail.mailers.{$driver}", []);

        $config = [
            'driver'       => $driver,
            'host'         => $mailerConfig['host']       ?? null,
            'port'         => $mailerConfig['port']       ?? null,
            'encryption'   => $mailerConfig['encryption'] ?? null,
            'username'     => $mailerConfig['username']   ?? null,
            'password_set' => filled($mailerConfig['password'] ?? null),
            'from_address' => config('mail.from.address'),
            'from_name'    => config('mail.from.name'),
            'queue'        => config('queue.default'),
        ];

        $stats = [
            'sent_7d'        => EmailLog::where('status', 'sent')->where('created_at', '>=', now()->subDays(7))->count(),
            'failed_7d'      => EmailLog::where('status', 'failed')->where('created_at', '>=', now()->subDays(7))->count(),
            'last_sent'      => EmailLog::where('status', 'sent')->latest()->first(),
            'last_failed'    => EmailLog::where('status', 'failed')->latest()->first(),
            'login_fail_24h' => AuthLog::whereIn('event', ['login_failed', 'login_blocked_pending', 'login_blocked_suspended'])
                ->where('created_at', '>=', now()->subDay())->count(),
        ];

        return view('pages.admin.email.index', [
            'config'      => $config,
            'stats'       => $stats,
            'emailLogs'   => EmailLog::with('user:id,name')->latest()->limit(25)->get(),
            'authLogs'    => AuthLog::with('user:id,name')->latest()->limit(25)->get(),
            'meta'        => ['title' => 'Email & Auth Logs — Admin | OmniReferral'],
        ]);
    }

    #[OA\Post(
        path: '/admin/email/test',
        tags: ['Admin', 'Email System'],
        summary: 'Send test email from admin panel',
        description: 'Sends a plain text test email to verify mail delivery. Admin only.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Test result',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'ok', type: 'boolean'),
                        new OA\Property(property: 'message', type: 'string'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function sendTest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $to = $validated['email'];

        try {
            Mail::raw(
                "This is a test email from your OmniReferral admin panel.\n\n"
                ."If you received this, your outgoing mail (driver: ".config('mail.default').") is working.\n\n"
                ."Sent at: ".now()->toDayDateTimeString(),
                function ($message) use ($to) {
                    $message->to($to)->subject('OmniReferral SMTP Test Email');
                }
            );

            // Success is auto-recorded by the LogSentEmail listener (status=sent).
            Log::info('Admin test email sent.', ['to' => $to, 'by' => $request->user()?->id]);

            return response()->json([
                'ok'      => true,
                'message' => 'Test email sent to '.$to.' via "'.config('mail.default').'" driver.',
            ]);
        } catch (\Throwable $e) {
            EmailLog::failed($to, $e->getMessage(), [
                'event_type' => 'test_email',
                'subject'    => 'OmniReferral SMTP Test Email',
                'user_id'    => $request->user()?->id,
            ]);

            Log::error('Admin test email failed.', ['to' => $to, 'error' => $e->getMessage()]);

            return response()->json([
                'ok'      => false,
                'message' => 'Send failed: '.$e->getMessage(),
            ]);
        }
    }

    #[OA\Post(
        path: '/admin/email/smtp-test',
        tags: ['Admin', 'Email System'],
        summary: 'Test SMTP connection from admin panel',
        description: 'Tests the TCP connection to the configured SMTP server. Admin only.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Connection test result',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'ok', type: 'boolean'),
                        new OA\Property(property: 'message', type: 'string'),
                    ]
                )
            ),
        ]
    )]
    public function smtpTest(Request $request): JsonResponse
    {
        $driver = config('mail.default');

        try {
            $transport = Mail::mailer()->getSymfonyTransport();

            if (! method_exists($transport, 'start')) {
                return response()->json([
                    'ok'      => true,
                    'message' => 'Driver "'.$driver.'" does not use a network connection (nothing to test). Use "Send Test Email" to verify delivery.',
                ]);
            }

            $transport->start();

            return response()->json([
                'ok'      => true,
                'message' => 'Connection to mail server succeeded (driver: '.$driver.').',
            ]);
        } catch (\Throwable $e) {
            Log::error('SMTP connection test failed.', ['driver' => $driver, 'error' => $e->getMessage()]);

            return response()->json([
                'ok'      => false,
                'message' => 'Connection failed: '.$e->getMessage(),
            ]);
        }
    }
}
