<?php

namespace App\Http\Controllers;

use App\Models\ServiceSeoPage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceSeoPageController extends Controller
{
    public function show(string $slug, Request $request): View
    {
        $query = ServiceSeoPage::bySlug($slug);

        if ($request->user()?->can('admin.access')) {
            $page = $query->firstOrFail();
        } else {
            $page = $query->published()->firstOrFail();
        }

        $meta = [
            'title' => $page->seo_title ?: $page->title . ' | OmniReferral',
            'description' => $page->meta_description,
        ];

        return view('pages.service-seo-page', compact('page', 'meta'));
    }
}
