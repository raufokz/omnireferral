<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        return view('pages.blog', [
            'blogs' => Blog::latest()->paginate(6),
            'meta' => [
                'title' => 'Blog | OmniReferral Real Estate Growth Insights',
                'description' => 'SEO-friendly real estate lead generation insights, referral strategies, and agent growth advice from OmniReferral.',
            ],
        ]);
    }

    public function show(Blog $blog): View
    {
        return view('pages.blog-show', [
            'blog' => $blog,
            'related' => Blog::where('id', '!=', $blog->id)->latest()->take(3)->get(),
            'meta' => [
                'title' => $blog->meta_title ?: $blog->title,
                'description' => $blog->meta_description ?: $blog->excerpt,
            ],
        ]);
    }
}
