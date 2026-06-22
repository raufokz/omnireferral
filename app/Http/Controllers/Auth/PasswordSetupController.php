<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthLog;
use App\Services\PasswordSetupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PasswordSetupRequest',
    type: 'object',
    required: ['password', 'password_confirmation'],
    properties: [
        new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
    ]
)]
class PasswordSetupController extends Controller
{
    public function __construct(
        private readonly PasswordSetupService $setupService,
    ) {}

    #[OA\Get(
        path: '/password/setup/{token}',
        tags: ['Auth'],
        summary: 'Show password setup form',
        description: 'Displays the one-time password setup form for a valid token.',
        parameters: [new OA\Parameter(name: 'token', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Password setup page'),
            new OA\Response(response: 302, description: 'Redirect to forgot-password if token invalid/expired'),
        ]
    )]
    public function show(string $token): View|RedirectResponse
    {
        $record = $this->setupService->findValid($token);

        if (! $record) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'This password setup link is invalid or has expired. Request a new reset link below.']);
        }

        return view('pages.auth.password-setup', [
            'token' => $token,
            'email' => $record->user?->email,
        ]);
    }

    #[OA\Post(
        path: '/password/setup/{token}',
        tags: ['Auth'],
        summary: 'Set password via setup token',
        description: 'Consumes a one-time setup token, sets the user\'s password, and logs them in.',
        parameters: [new OA\Parameter(name: 'token', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/PasswordSetupRequest')
        ),
        responses: [
            new OA\Response(response: 302, description: 'Redirect to user dashboard'),
            new OA\Response(response: 422, description: 'Validation error or expired token'),
        ]
    )]
    public function store(Request $request, string $token): RedirectResponse
    {
        $record = $this->setupService->findValid($token);

        if (! $record) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'This password setup link is invalid or has expired. Request a new reset link below.']);
        }

        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $this->setupService->consume($record, $request->string('password')->value());

        AuthLog::record('password_set', 'success', [
            'user_id' => $user->id, 'email' => $user->email, 'request' => $request,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->to($user->dashboardRoute())
            ->with('success', 'Your password has been set. Welcome to your OmniReferral portal!');
    }
}
