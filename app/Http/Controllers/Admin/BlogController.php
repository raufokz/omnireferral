<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $this->authorizeBlogManagement();

        return view('pages.admin.blog.index', [
            'blogs' => Blog::latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        $this->authorizeBlogManagement();

        return view('pages.admin.blog.create');
    }

    public function store(Request $request)
    {
        $this->authorizeBlogManagement();
        $this->normalizeSlugInput($request);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('blogs', 'slug')],
            'category' => 'required|string|max:100',
            'excerpt' => 'required|string',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['author'] = $request->user()->name;
        $validated['user_id'] = $request->user()->id;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('blogs', 'public');
        }

        Blog::create($validated);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post created successfully.');
    }

    public function edit(Blog $blog): View
    {
        $this->authorizeBlogManagement();

        return view('pages.admin.blog.edit', compact('blog'));
    }

    public function update(Request $request, Blog $blog)
    {
        $this->authorizeBlogManagement();
        $this->normalizeSlugInput($request);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('blogs', 'slug')->ignore($blog->id)],
            'category' => 'required|string|max:100',
            'excerpt' => 'required|string',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['author'] = $request->user()->name;
        $validated['user_id'] = $request->user()->id;

        if ($request->hasFile('image')) {
            if ($blog->image) {
                Storage::disk('public')->delete($blog->image);
            }
            $validated['image'] = $request->file('image')->store('blogs', 'public');
        }

        $blog->update($validated);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post updated successfully.');
    }

    public function destroy(Blog $blog)
    {
        $this->authorizeBlogManagement();

        if ($blog->image) {
            Storage::disk('public')->delete($blog->image);
        }

        $blog->delete();
        return redirect()->route('admin.blog.index')->with('success', 'Blog post deleted successfully.');
    }

    private function authorizeBlogManagement(): void
    {
        abort_unless(request()->user()?->can('blog.manage'), 403);
    }

    private function normalizeSlugInput(Request $request): void
    {
        $slug = Str::slug((string) $request->input('slug'));

        if ($slug === '') {
            $slug = Str::slug((string) $request->input('title'));
        }

        $request->merge(['slug' => $slug]);
    }
}
