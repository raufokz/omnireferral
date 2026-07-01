<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RealtorProfile;
use App\Models\SeoLandingPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'hero_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'og_image' => 'nullable|string|max:500',
            'realtor_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_published' => 'boolean',
            'content' => 'nullable|array',
        ]);

        $validated['is_published'] = $request->boolean('is_published');

        $validated['hero_image'] = $this->handleImageUpload($request, 'hero_image', 'seo-landing-pages/hero', null);
        $validated['realtor_photo'] = $this->handleImageUpload($request, 'realtor_photo', 'seo-landing-pages/realtor', null);

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
            'hero_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'og_image' => 'nullable|string|max:500',
            'realtor_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_published' => 'boolean',
            'content' => 'nullable|array',
        ]);

        $validated['is_published'] = $request->boolean('is_published');

        $validated['hero_image'] = $this->handleImageUpload($request, 'hero_image', 'seo-landing-pages/hero', $seoLandingPage->hero_image);
        $validated['realtor_photo'] = $this->handleImageUpload($request, 'realtor_photo', 'seo-landing-pages/realtor', $seoLandingPage->realtor_photo);

        if ($request->has('secondary_keywords')) {
            $validated['secondary_keywords'] = $this->linesToArray($request->input('secondary_keywords'));
        }

        if (isset($validated['content'])) {
            $validated['content'] = $this->normalizeContent($validated['content']);
        }

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug('best-realtor-' . $validated['city'] . '-' . $validated['state']);
        } else {
            $validated['slug'] = Str::slug($validated['slug']);
        }

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
        $this->deleteStoredImage($seoLandingPage->hero_image);
        $this->deleteStoredImage($seoLandingPage->realtor_photo);

        $seoLandingPage->delete();

        return redirect()->route('admin.seo-landing-pages.index')
            ->with('success', 'SEO landing page deleted successfully.');
    }

    private function handleImageUpload(Request $request, string $field, string $subdirectory, ?string $currentValue): ?string
    {
        if ($request->hasFile($field)) {
            if ($currentValue && str_starts_with((string) $currentValue, 'storage/seo-landing-pages/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', (string) $currentValue));
            }

            $path = $request->file($field)->store($subdirectory, 'public');

            return $path ? 'storage/' . $path : $currentValue;
        }

        if ($request->input($field . '_remove') === '1' && $currentValue) {
            if (str_starts_with((string) $currentValue, 'storage/seo-landing-pages/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', (string) $currentValue));
            }
            return null;
        }

        return $currentValue;
    }

    private function deleteStoredImage(?string $path): void
    {
        if ($path && str_starts_with($path, 'storage/seo-landing-pages/')) {
            Storage::disk('public')->delete(str_replace('storage/', '', $path));
        }
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
