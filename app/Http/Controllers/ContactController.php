<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Property;
use App\Models\RealtorProfile;
use App\Services\EnquiryFromContactService;
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
            'subject' => ['nullable', 'string', 'max:255'],
            'recipient_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'realtor_profile_id' => ['nullable', 'integer', 'exists:realtor_profiles,id'],
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'source' => ['nullable', 'string', 'max:100'],
            'message' => ['required', 'string'],
        ], [
            'email.required' => 'Oops, looks like you missed your email!',
        ]);

        $property = null;
        $realtorProfile = null;

        if (! empty($validated['property_id'])) {
            $property = Property::query()
                ->with('realtorProfile.user')
                ->find($validated['property_id']);

            $realtorProfile = $property?->realtorProfile;
        }

        if (! $realtorProfile && ! empty($validated['realtor_profile_id'])) {
            $realtorProfile = RealtorProfile::query()
                ->with('user')
                ->find($validated['realtor_profile_id']);
        }

        $recipientUserId = $realtorProfile?->user_id ?: ($validated['recipient_user_id'] ?? null);

        if (($property || $realtorProfile) && ! $recipientUserId) {
            return back()
                ->withInput()
                ->with('error', 'This agent is not available for direct contact right now.');
        }

        $subject = trim((string) ($validated['subject'] ?? ''));
        $senderRole = $validated['role'] ?? $request->user()?->role;

        if ($subject === '') {
            $subject = $property
                ? 'Inquiry about ' . $property->title
                : ($realtorProfile ? 'Inquiry for ' . ($realtorProfile->user->name ?? 'Agent') : 'General contact inquiry');
        }

        $contact = Contact::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $senderRole,
            'zip_code' => $validated['zip_code'] ?? null,
            'subject' => $subject,
            'message' => $validated['message'],
            'source' => $validated['source']
                ?? ($property ? 'website_property_inquiry' : ($realtorProfile ? 'website_agent_profile' : 'website_contact')),
            'recipient_user_id' => $recipientUserId,
            'realtor_profile_id' => $realtorProfile?->id,
            'property_id' => $property?->id,
            'message_status' => 'new',
        ]);

        if ($property && $recipientUserId) {
            EnquiryFromContactService::createFromContact(
                $contact->fresh(['property', 'realtorProfile.user']),
                $request->user()?->id
            );
        }

        $successMessage = $recipientUserId
            ? ($property
                ? 'Your message has been sent to the listing agent. The property owner and OmniReferral operations have also been notified by email.'
                : 'Your message has been sent directly to this agent.')
            : 'Thanks for reaching out. We will follow up with you shortly.';

        return back()->with('success', $successMessage);
    }
}
