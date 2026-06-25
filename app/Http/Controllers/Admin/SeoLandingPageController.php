<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RealtorProfile;
use App\Models\SeoLandingPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SeoLandingPageController extends Controller
{
    public function index(): View
    {
        $pages = SeoLandingPage::with('realtorProfile.user')->latest()->paginate(25);

        return view('pages.admin.seo-landing-pages.index', compact('pages'));
    }

    public function edit(SeoLandingPage $seoLandingPage): View
    {
        $seoLandingPage->load('realtorProfile.user');

        $realtorProfiles = RealtorProfile::query()
            ->with('user:id,name,display_name,email')
            ->orderBy('service_state')
            ->orderBy('service_city')
            ->get();

        return view('pages.admin.seo-landing-pages.edit', [
            'page' => $seoLandingPage,
            'realtorProfiles' => $realtorProfiles,
        ]);
    }

    public function create(): View
    {
        $realtorProfiles = RealtorProfile::query()
            ->with('user:id,name,display_name,email')
            ->orderBy('service_state')
            ->orderBy('service_city')
            ->get();

        return view('pages.admin.seo-landing-pages.create', compact('realtorProfiles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'realtor_profile_id' => 'nullable|exists:realtor_profiles,id',
            'city' => 'required|string|max:100',
            'state' => 'required|string|size:2',
            'primary_keyword' => 'required|string|max:255',
            'secondary_keywords' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|url|max:500',
            'hero_image' => 'nullable|string|max:500',
            'og_image' => 'nullable|string|max:500',
            'is_published' => 'boolean',
            'content' => 'nullable|array',
        ]);

        $validated['is_published'] = $request->boolean('is_published');

        if ($request->has('secondary_keywords')) {
            $validated['secondary_keywords'] = $this->linesToArray($request->input('secondary_keywords'));
        }

        if (isset($validated['content'])) {
            $validated['content'] = $this->normalizeContent($validated['content']);
        }

        $validated['slug'] = Str::slug('best-realtor-' . $validated['city'] . '-' . $validated['state']);

        $existing = SeoLandingPage::bySlug($validated['slug'])->first();
        if ($existing) {
            $validated['slug'] = $validated['slug'] . '-' . Str::random(4);
        }

        $page = SeoLandingPage::create($validated);

        return redirect()->route('admin.seo-landing-pages.edit', $page)
            ->with('success', 'SEO landing page created successfully.');
    }

    public function update(Request $request, SeoLandingPage $seoLandingPage): RedirectResponse
    {
        $validated = $request->validate([
            'realtor_profile_id' => 'nullable|exists:realtor_profiles,id',
            'slug' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|size:2',
            'primary_keyword' => 'required|string|max:255',
            'secondary_keywords' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|url|max:500',
            'hero_image' => 'nullable|string|max:500',
            'og_image' => 'nullable|string|max:500',
            'is_published' => 'boolean',
            'content' => 'nullable|array',
        ]);

        $validated['is_published'] = $request->boolean('is_published');

        if ($request->has('secondary_keywords')) {
            $validated['secondary_keywords'] = $this->linesToArray($request->input('secondary_keywords'));
        }

        if (isset($validated['content'])) {
            $validated['content'] = $this->normalizeContent($validated['content']);
        }

        // Handle slug changes
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug('best-realtor-' . $validated['city'] . '-' . $validated['state']);
        } else {
            $validated['slug'] = Str::slug($validated['slug']);
        }

        // Check if slug already exists (excluding current page)
        $existing = SeoLandingPage::bySlug($validated['slug'])
            ->where('id', '!=', $seoLandingPage->id)
            ->first();
        if ($existing) {
            $validated['slug'] = $validated['slug'] . '-' . Str::random(4);
        }

        $seoLandingPage->update($validated);

        return redirect()->route('admin.seo-landing-pages.edit', $seoLandingPage)
            ->with('success', 'SEO landing page updated successfully.');
    }

    public function destroy(SeoLandingPage $seoLandingPage): RedirectResponse
    {
        $seoLandingPage->delete();

        return redirect()->route('admin.seo-landing-pages.index')
            ->with('success', 'SEO landing page deleted successfully.');
    }

    private function normalizeContent(array $content): array
    {
        if (array_key_exists('service_areas', $content)) {
            $content['service_areas'] = $this->linesToArray($content['service_areas']);
        }

        if (isset($content['faqs']) && is_array($content['faqs'])) {
            $content['faqs'] = collect($content['faqs'])
                ->map(fn ($faq) => [
                    'question' => trim((string) ($faq['question'] ?? '')),
                    'answer' => trim((string) ($faq['answer'] ?? '')),
                ])
                ->filter(fn ($faq) => $faq['question'] !== '' && $faq['answer'] !== '')
                ->values()
                ->all();
        }

        return $content;
    }

    private function linesToArray(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map(
                fn ($item) => trim((string) $item),
                $value
            )));
        }

        return array_values(array_filter(array_map(
            fn ($item) => trim((string) $item),
            preg_split('/\r\n|\r|\n/', (string) $value)
        )));
    }
}
