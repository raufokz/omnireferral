<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.testimonials.index', [
            'testimonials' => Testimonial::query()
                ->orderByRaw("case when submission_status = 'pending' then 0 when submission_status = 'approved' then 1 else 2 end")
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->latest()
                ->paginate(12),
            'stats' => [
                'total' => Testimonial::count(),
                'videos' => Testimonial::whereNotNull('video_url')->where('video_url', '!=', '')->count(),
                'published' => Testimonial::where('is_published', true)->count(),
                'pending' => Testimonial::pendingReview()->count(),
                'rejected' => Testimonial::where('submission_status', Testimonial::STATUS_REJECTED)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.testimonials.create', [
            'testimonial' => new Testimonial([
                'rating' => 5,
                'audience' => request('audience', 'agent'),
                'is_featured' => request()->boolean('video'),
                'is_published' => true,
                'sort_order' => 0,
                'submission_status' => Testimonial::STATUS_APPROVED,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $testimonial = new Testimonial();
        $testimonial->fill($this->validatedPayload($request));

        if ($request->hasFile('photo')) {
            $testimonial->photo = $request->file('photo')->store('testimonials/photos', 'public');
        }

        if ($request->hasFile('video_file')) {
            $testimonial->video_url = $request->file('video_file')->store('testimonials/videos', 'public');
        }

        $testimonial->submission_status = Testimonial::STATUS_APPROVED;
        $testimonial->reviewed_by_user_id = $request->user()?->id;
        $testimonial->reviewed_at = now();

        $testimonial->save();

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', 'Testimonial created successfully.');
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('pages.admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->fill($this->validatedPayload($request));

        if ($request->boolean('remove_photo')) {
            $testimonial->photo = null;
        }

        if ($request->boolean('remove_video')) {
            $testimonial->video_url = null;
        }

        if ($request->hasFile('photo')) {
            $testimonial->photo = $request->file('photo')->store('testimonials/photos', 'public');
        }

        if ($request->hasFile('video_file')) {
            $testimonial->video_url = $request->file('video_file')->store('testimonials/videos', 'public');
        }

        if ($request->boolean('is_published')) {
            $testimonial->submission_status = Testimonial::STATUS_APPROVED;
            $testimonial->reviewed_by_user_id = $request->user()?->id;
            $testimonial->reviewed_at = now();
        }

        $testimonial->save();

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', 'Testimonial deleted successfully.');
    }

    public function review(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
        ]);

        $isApproval = $validated['decision'] === 'approve';

        $testimonial->update([
            'submission_status' => $isApproval ? Testimonial::STATUS_APPROVED : Testimonial::STATUS_REJECTED,
            'is_published' => $isApproval,
            'reviewed_by_user_id' => $request->user()?->id,
            'reviewed_at' => now(),
            'is_featured' => $isApproval ? $testimonial->is_featured : false,
        ]);

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', $isApproval
                ? 'Review approved and published successfully.'
                : 'Review rejected successfully.');
    }

    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'audience' => ['required', 'in:buyer,seller,agent,community'],
            'company' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'quote' => ['required', 'string', 'max:3000'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'video_url' => ['nullable', 'string', 'max:2048'],
            'video_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:51200'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'remove_photo' => ['nullable', 'boolean'],
            'remove_video' => ['nullable', 'boolean'],
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_published'] = $request->boolean('is_published', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        unset($validated['photo'], $validated['video_file'], $validated['remove_photo'], $validated['remove_video']);

        return $validated;
    }
}
