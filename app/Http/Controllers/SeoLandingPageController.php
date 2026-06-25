<?php

namespace App\Http\Controllers;

use App\Models\SeoLandingPage;
use App\Models\SeoLandingPageLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoLandingPageController extends Controller
{
    public function show(string $slug): View|RedirectResponse
    {
        $page = SeoLandingPage::published()
            ->with(['realtorProfile.user', 'realtorProfile.properties'])
            ->bySlug($slug)
            ->firstOrFail();

        $meta = [
            'title' => $page->seo_title,
            'description' => $page->meta_description,
            'og_image' => $page->og_image,
        ];

        return view('pages.seo-landing-page', compact('page', 'meta'));
    }

    public function storeLead(Request $request, string $slug): RedirectResponse
    {
        $page = SeoLandingPage::published()->bySlug($slug)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'interest' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:5000',
        ]);

        $validated['seo_landing_page_id'] = $page->id;

        SeoLandingPageLead::create($validated);

        return redirect()->route('seo-landing-page.show', $page->slug)
            ->with('success', 'Thank you! A local real estate expert will reach out shortly.');
    }
}
