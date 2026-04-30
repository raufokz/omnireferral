<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlatformSearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()?->isStaff(), 403);

        $q = trim((string) $request->query('q', ''));
        $limit = 15;

        $users = collect();
        $properties = collect();
        $enquiries = collect();

        if ($q !== '') {
            $users = User::query()
                ->where(function ($query) use ($q) {
                    $query->where('name', 'like', '%' . $q . '%')
                        ->orWhere('email', 'like', '%' . $q . '%');
                })
                ->orderByDesc('id')
                ->limit($limit)
                ->get(['id', 'name', 'email', 'role', 'status']);

            $properties = Property::query()
                ->where(function ($query) use ($q) {
                    $query->where('title', 'like', '%' . $q . '%')
                        ->orWhere('location', 'like', '%' . $q . '%')
                        ->orWhere('zip_code', 'like', '%' . $q . '%');
                })
                ->latest()
                ->limit($limit)
                ->get(['id', 'title', 'slug', 'status', 'zip_code']);

            $enquiries = Enquiry::query()
                ->where(function ($query) use ($q) {
                    $query->where('sender_name', 'like', '%' . $q . '%')
                        ->orWhere('sender_email', 'like', '%' . $q . '%')
                        ->orWhere('subject', 'like', '%' . $q . '%');
                })
                ->latest()
                ->limit($limit)
                ->get(['id', 'sender_name', 'sender_email', 'subject', 'status', 'created_at']);
        }

        return view('pages.admin.search', [
            'query' => $q,
            'users' => $users,
            'properties' => $properties,
            'enquiries' => $enquiries,
            'isStaffView' => $request->user()->role === 'staff',
            'meta' => [
                'title' => 'Platform Search | OmniReferral',
                'description' => 'Search users, listings, and enquiries from one admin entry point.',
            ],
        ]);
    }
}
