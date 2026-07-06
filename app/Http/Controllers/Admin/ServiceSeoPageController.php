<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceSeoPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServiceSeoPageController extends Controller
{
    public function index(): View
    {
        $pages = ServiceSeoPage::latest()->paginate(25);

        return view('pages.admin.service-seo-pages.index', compact('pages'));
    }

    public function create(): View
    {
        return view('pages.admin.service-seo-pages.create', [
            'page' => new ServiceSeoPage([
                'cta_label' => 'Get Your First Leads Today',
                'cta_url' => '/contact',
                'is_published' => false,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedPage($request);
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?: $validated['title']);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['secondary_keywords'] = $this->linesToArray($request->input('secondary_keywords'));
        $validated['content'] = $this->normalizeContent($request->input('content', []), $request);

        $page = ServiceSeoPage::create($validated);

        return redirect()->route('admin.service-seo-pages.edit', $page)
            ->with('success', 'Service SEO page created successfully.');
    }

    public function edit(ServiceSeoPage $serviceSeoPage): View
    {
        return view('pages.admin.service-seo-pages.edit', ['page' => $serviceSeoPage]);
    }

    public function update(Request $request, ServiceSeoPage $serviceSeoPage): RedirectResponse
    {
        $validated = $this->validatedPage($request);
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?: $validated['title'], $serviceSeoPage->id);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['secondary_keywords'] = $this->linesToArray($request->input('secondary_keywords'));
        $validated['content'] = $this->normalizeContent($request->input('content', []), $request, $serviceSeoPage);

        $serviceSeoPage->update($validated);

        return redirect()->route('admin.service-seo-pages.edit', $serviceSeoPage)
            ->with('success', 'Service SEO page updated successfully.');
    }

    public function destroy(ServiceSeoPage $serviceSeoPage): RedirectResponse
    {
        $serviceSeoPage->delete();

        return redirect()->route('admin.service-seo-pages.index')
            ->with('success', 'Service SEO page deleted successfully.');
    }

    public function togglePublish(ServiceSeoPage $serviceSeoPage): RedirectResponse
    {
        $serviceSeoPage->update(['is_published' => ! $serviceSeoPage->is_published]);

        $status = $serviceSeoPage->is_published ? 'published' : 'unpublished';

        return redirect()->route('admin.service-seo-pages.index')
            ->with('success', "Service SEO page \"{$serviceSeoPage->title}\" has been {$status}.");
    }

    private function validatedPage(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|string|max:500',
            'primary_keyword' => 'nullable|string|max:255',
            'secondary_keywords' => 'nullable|string',
            'hero_title' => 'nullable|string|max:255',
            'hero_body' => 'nullable|string',
            'cta_label' => 'nullable|string|max:100',
            'cta_url' => 'nullable|string|max:500',
            'is_published' => 'boolean',
            'content' => 'nullable|array',
        ]);
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'service-page';
        $slug = $base;
        $counter = 2;

        while (ServiceSeoPage::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function normalizeContent(array $content, ?Request $request = null, ?ServiceSeoPage $page = null): array
    {
        $heroImage = trim((string) ($content['hero_image'] ?? ''));

        if ($request) {
            $heroImage = $this->handleHeroImageUpload($request, $page ? $page->content['hero_image'] ?? '' : '');
        }

        $sections = collect($content['sections'] ?? [])
            ->map(function ($section, $index) use ($request) {
                $image = trim((string) ($section['image'] ?? ''));

                if ($request && ! empty($section['remove_image'])) {
                    $this->deleteStoredImage($image);
                    $image = '';
                }

                if ($request && $request->hasFile("upload_section_image.{$index}")) {
                    $this->deleteStoredImage($image);
                    $path = $request->file("upload_section_image.{$index}")->store('service-seo', 'public');
                    $image = $path ? 'storage/' . $path : '';
                }

                return [
                    'heading' => trim((string) ($section['heading'] ?? '')),
                    'body' => trim((string) ($section['body'] ?? '')),
                    'image' => $image,
                ];
            })
            ->filter(fn ($section) => $section['heading'] !== '' || ! $this->isRichTextEmpty($section['body']))
            ->values()
            ->all();

        $faqs = collect($content['faqs'] ?? [])
            ->map(fn ($faq) => [
                'question' => trim((string) ($faq['question'] ?? '')),
                'answer' => trim((string) ($faq['answer'] ?? '')),
            ])
            ->filter(fn ($faq) => $faq['question'] !== '' && ! $this->isRichTextEmpty($faq['answer']))
            ->values()
            ->all();

        return compact('heroImage', 'sections', 'faqs');
    }

    private function handleHeroImageUpload(Request $request, string $currentValue): string
    {
        if ($request->boolean('remove_hero_image')) {
            $this->deleteStoredImage($currentValue);
            return '';
        }

        if ($request->hasFile('upload_hero_image')) {
            $this->deleteStoredImage($currentValue);
            $path = $request->file('upload_hero_image')->store('service-seo', 'public');
            return $path ? 'storage/' . $path : $currentValue;
        }

        return $currentValue;
    }

    private function deleteStoredImage(?string $path): void
    {
        if ($path && str_starts_with((string) $path, 'storage/service-seo/')) {
            Storage::disk('public')->delete(str_replace('storage/', '', (string) $path));
        }
    }

    private function isRichTextEmpty(string $value): bool
    {
        return trim(strip_tags(html_entity_decode($value))) === '';
    }

    private function linesToArray(mixed $value): array
    {
        return array_values(array_filter(array_map(
            fn ($item) => trim((string) $item),
            preg_split('/\r\n|\r|\n/', (string) $value)
        )));
    }
}
