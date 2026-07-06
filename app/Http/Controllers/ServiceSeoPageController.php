<?php

namespace App\Http\Controllers;

use App\Models\ServiceSeoPage;
use Illuminate\View\View;

class ServiceSeoPageController extends Controller
{
    public function show(string $slug): View
    {
        $page = ServiceSeoPage::published()->bySlug($slug)->firstOrFail();

        $meta = [
            'title' => $page->seo_title ?: $page->title . ' | OmniReferral',
            'description' => $page->meta_description,
        ];

        return view('pages.service-seo-page', compact('page', 'meta'));
    }
}
