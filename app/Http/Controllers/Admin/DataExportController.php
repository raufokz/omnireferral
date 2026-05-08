<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class DataExportController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('admin.access'), 403);

        $exports = DataExport::query()
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('pages.admin.exports.index', [
            'exports' => $exports,
            'meta' => [
                'title' => 'Data exports | OmniReferral',
                'description' => 'Queued exports generated in the background for large datasets.',
            ],
        ]);
    }

    public function download(Request $request, DataExport $export): BinaryFileResponse
    {
        abort_unless($request->user()?->can('admin.access'), 403);
        abort_unless($export->status === 'complete' && $export->file_path, 404);

        $path = $export->file_path;
        abort_unless(Storage::disk('local')->exists($path), 404);

        $fullPath = Storage::disk('local')->path($path);
        $filename = basename($path);

        return response()->download($fullPath, $filename, array_filter([
            'Content-Type' => $export->content_type ?: null,
        ]));
    }
}

