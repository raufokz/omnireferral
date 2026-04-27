<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PropertyCommentController extends Controller
{
    public function store(Request $request, Property $property): RedirectResponse
    {
        abort_unless($property->isApproved() && $property->status === 'Active', 404);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'author_name' => [
                Rule::requiredIf(fn () => $request->user() === null),
                'nullable',
                'string',
                'max:120',
            ],
        ]);

        $user = $request->user();

        PropertyComment::create([
            'property_id' => $property->id,
            'user_id' => $user?->id,
            'author_name' => $user ? null : ($validated['author_name'] ?? null),
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Your comment has been posted. Thank you for the feedback.');
    }
}
