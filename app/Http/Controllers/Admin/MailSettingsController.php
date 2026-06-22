<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class MailSettingsController extends Controller
{
    public function index(): View
    {
        $settings = MailSetting::instance();

        $driver = $settings->mailer ?: config('mail.default');

        $effective = [
            'mailer'             => $driver,
            'host'               => $settings->host ?: config("mail.mailers.{$driver}.host"),
            'port'               => $settings->port ?: config("mail.mailers.{$driver}.port"),
            'encryption'         => $settings->encryption ?: config("mail.mailers.{$driver}.encryption"),
            'username'           => $settings->username ?: config("mail.mailers.{$driver}.username"),
            'password_set'       => filled($settings->password) || filled(config("mail.mailers.{$driver}.password")),
            'from_address'       => $settings->from_address ?: config('mail.from.address'),
            'from_name'          => $settings->from_name ?: config('mail.from.name'),
            'credentials_from_address' => $settings->credentials_from_address ?: config('mail.credentials_from.address'),
            'credentials_from_name'    => $settings->credentials_from_name ?: config('mail.credentials_from.name'),
        ];

        return view('pages.admin.mail-settings.index', [
            'settings'  => $settings,
            'effective' => $effective,
            'canEdit'   => auth()->user()?->isSuperAdmin(),
            'meta'      => ['title' => 'Mail Settings — Admin | OmniReferral'],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'Only super admins can edit mail settings.');

        $validated = $request->validate([
            'mailer'                    => 'required|in:smtp,sendmail,ses,postmark,resend,log,mailgun',
            'host'                      => 'nullable|string|max:255',
            'port'                      => 'nullable|integer|min:1|max:65535',
            'encryption'                => 'nullable|in:tls,ssl,null|max:20',
            'username'                  => 'nullable|string|max:255',
            'password'                  => 'nullable|string|max:500',
            'from_address'              => 'nullable|email|max:255',
            'from_name'                 => 'nullable|string|max:255',
            'credentials_from_address'  => 'nullable|email|max:255',
            'credentials_from_name'     => 'nullable|string|max:255',
        ]);

        $settings = MailSetting::instance();

        // Normalise null encryption
        if (($validated['encryption'] ?? '') === 'null') {
            $validated['encryption'] = null;
        }

        // Preserve existing encrypted password if left blank
        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $validated['connection_status'] = 'unknown';

        $settings->fill($validated);
        $settings->save();

        // Clear cached config so the new settings take effect immediately
        $this->applyMailConfig($settings);

        Log::info('Mail settings updated.', ['by_user_id' => $request->user()->id]);

        return redirect()
            ->route('admin.mail-settings.index')
            ->with('success', 'Mail settings saved. Use the test tools below to verify delivery.');
    }

    public function testConnection(Request $request): JsonResponse
    {
        $settings = MailSetting::instance();

        try {
            $transport = Mail::mailer()->getSymfonyTransport();

            if (! method_exists($transport, 'start')) {
                return response()->json([
                    'ok'      => true,
                    'message' => 'Driver "'.$settings->mailer.'" does not use a network connection. Use "Send Test Email" to verify delivery.',
                ]);
            }

            $transport->start();

            $settings->connection_status = 'connected';
            $settings->last_tested_at = now();
            $settings->last_tested_by_user_id = $request->user()?->id;
            $settings->save();

            return response()->json([
                'ok'      => true,
                'message' => 'Connection to mail server succeeded (driver: '.$settings->mailer.').',
            ]);
        } catch (\Throwable $e) {
            $settings->connection_status = 'error';
            $settings->last_tested_at = now();
            $settings->last_tested_by_user_id = $request->user()?->id;
            $settings->save();

            Log::error('Mail connection test failed.', ['error' => $e->getMessage()]);

            return response()->json([
                'ok'      => false,
                'message' => 'Connection failed: '.$e->getMessage(),
            ]);
        }
    }

    public function sendTest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $settings = MailSetting::instance();
        $to = $validated['email'];

        try {
            Mail::raw(
                "This is a test email from your OmniReferral admin panel.\n\n"
                ."If you received this, your mail settings are working correctly.\n\n"
                ."Sent at: ".now()->toDayDateTimeString(),
                function ($message) use ($to) {
                    $message->to($to)->subject('OmniReferral Mail Settings Test');
                }
            );

            Log::info('Admin mail settings test email sent.', ['to' => $to, 'by' => $request->user()?->id]);

            return response()->json([
                'ok'      => true,
                'message' => 'Test email sent to '.$to.' via "'.($settings->mailer ?? config('mail.default')).'" driver.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin mail settings test email failed.', ['to' => $to, 'error' => $e->getMessage()]);

            return response()->json([
                'ok'      => false,
                'message' => 'Send failed: '.$e->getMessage(),
            ]);
        }
    }

    private function applyMailConfig(MailSetting $settings): void
    {
        if ($settings->mailer) {
            config(['mail.default' => $settings->mailer]);
        }

        $driver = $settings->mailer;

        if ($settings->host) {
            config(["mail.mailers.{$driver}.host" => $settings->host]);
        }
        if ($settings->port) {
            config(["mail.mailers.{$driver}.port" => (int) $settings->port]);
        }
        if ($settings->encryption !== null) {
            config(["mail.mailers.{$driver}.encryption" => $settings->encryption ?: null]);
        }
        if ($settings->username) {
            config(["mail.mailers.{$driver}.username" => $settings->username]);
        }
        if ($settings->password) {
            config(["mail.mailers.{$driver}.password" => $settings->password]);
        }
        if ($settings->from_address) {
            config(['mail.from.address' => $settings->from_address]);
        }
        if ($settings->from_name) {
            config(['mail.from.name' => $settings->from_name]);
        }
        if ($settings->credentials_from_address) {
            config(['mail.credentials_from.address' => $settings->credentials_from_address]);
        }
        if ($settings->credentials_from_name) {
            config(['mail.credentials_from.name' => $settings->credentials_from_name]);
        }
    }
}
