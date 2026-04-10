<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $selectedAudience = $request->string('audience')->lower()->value();
        $selectedAudience = in_array($selectedAudience, ['buyer', 'seller', 'agent', 'community'], true) ? $selectedAudience : 'all';
        $user = $request->user();

        $publishedTestimonials = Testimonial::published()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->latest()
            ->get();

        $counts = [
            'all' => $publishedTestimonials->count(),
            'buyer' => $publishedTestimonials->where('audience', 'buyer')->count(),
            'seller' => $publishedTestimonials->where('audience', 'seller')->count(),
            'agent' => $publishedTestimonials->where('audience', 'agent')->count(),
            'community' => $publishedTestimonials->where('audience', 'community')->count(),
        ];

        $groupedTestimonials = [
            'buyer' => $publishedTestimonials->where('audience', 'buyer')->values(),
            'seller' => $publishedTestimonials->where('audience', 'seller')->values(),
            'agent' => $publishedTestimonials->where('audience', 'agent')->values(),
            'community' => $publishedTestimonials->where('audience', 'community')->values(),
        ];

        $visibleTestimonials = $selectedAudience === 'all'
            ? $publishedTestimonials
            : $publishedTestimonials->where('audience', $selectedAudience)->values();

        $featuredTestimonials = $visibleTestimonials
            ->filter(fn (Testimonial $testimonial) => $testimonial->is_featured)
            ->take(3);

        if ($featuredTestimonials->count() < 3) {
            $featuredTestimonials = $featuredTestimonials->concat(
                $visibleTestimonials
                    ->reject(fn (Testimonial $testimonial) => $featuredTestimonials->contains('id', $testimonial->id))
                    ->take(3 - $featuredTestimonials->count())
            );
        }

        $featuredTestimonials = $featuredTestimonials->values();

        $videoTestimonials = $visibleTestimonials
            ->filter(fn (Testimonial $testimonial) => $testimonial->has_video)
            ->take(6)
            ->values();

        $averageRating = round((float) ($visibleTestimonials->avg('rating') ?: $publishedTestimonials->avg('rating') ?: 5), 1);

        return view('pages.testimonials', [
            'selectedAudience' => $selectedAudience,
            'counts' => $counts,
            'groupedTestimonials' => $groupedTestimonials,
            'videoTestimonials' => $videoTestimonials,
            'featuredTestimonials' => $featuredTestimonials,
            'averageRating' => $averageRating,
            'testimonials' => $visibleTestimonials,
            'reviewAudienceOptions' => [
                'buyer' => 'Buyer',
                'seller' => 'Seller',
                'agent' => 'Agent',
                'community' => 'Community Member',
            ],
            'reviewDraft' => [
                'name' => old('name', $user?->name),
                'email' => old('email', $user?->email),
                'audience' => old('audience', $this->defaultAudienceFor($user?->role)),
                'company' => old('company', $user?->roleLabel()),
                'location' => old('location', collect([$user?->city, $user?->state])->filter()->implode(', ')),
                'rating' => old('rating', 5),
                'quote' => old('quote'),
            ],
            'meta' => [
                'title' => 'Testimonials | OmniReferral',
                'description' => 'Read reviews and watch testimonials from buyers, sellers, and agents who use OmniReferral.',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'audience' => ['required', 'in:buyer,seller,agent,community'],
            'company' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'quote' => ['required', 'string', 'max:3000'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        $testimonial = new Testimonial([
            'name' => $validated['name'],
            'audience' => $validated['audience'],
            'company' => $validated['company'] ?: ($user?->roleLabel() ?? null),
            'location' => $validated['location'] ?: collect([$user?->city, $user?->state])->filter()->implode(', '),
            'submitted_by_email' => $validated['email'],
            'submitted_by_user_id' => $user?->id,
            'rating' => $validated['rating'],
            'quote' => $validated['quote'],
            'is_featured' => false,
            'is_published' => false,
            'sort_order' => 0,
            'submission_status' => Testimonial::STATUS_PENDING,
        ]);

        if ($request->hasFile('photo')) {
            $testimonial->photo = $request->file('photo')->store('testimonials/photos', 'public');
        } elseif ($user?->avatar) {
            $testimonial->photo = 'storage/' . ltrim($user->avatar, '/');
        }

        $testimonial->save();

        return redirect()
            ->route('reviews', ['audience' => $validated['audience']])
            ->with('success', 'Your review has been sent to the OmniReferral admin team for approval.');
    }

    private function defaultAudienceFor(?string $role): string
    {
        return match ($role) {
            'buyer' => 'buyer',
            'seller' => 'seller',
            'agent' => 'agent',
            default => 'community',
        };
    }
}
