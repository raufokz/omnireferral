<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('pages.contact', [
            'meta' => [
                'title' => 'Contact OmniReferral',
                'description' => 'Get in touch with OmniReferral for real estate leads, partnerships, and support.',
            ],
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['nullable', 'string', 'max:100'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'source' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string'],
        ], [
            'email.required' => 'Oops, looks like you missed your email!',
        ]);

        Contact::create($validated);

        return back()->with('success', 'Thanks for reaching out. We will follow up with you shortly.');
    }
}
