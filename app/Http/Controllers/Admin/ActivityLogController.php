<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $filters = [
            'action' => trim((string) $request->query('action', '')),
            'actor' => (string) $request->query('actor', ''),
            'date_from' => (string) $request->query('date_from', ''),
            'date_to' => (string) $request->query('date_to', ''),
        ];

        $query = AdminActivityLog::query()->with('actor:id,name,email')->latest('created_at');

        if ($filters['action'] !== '') {
            $query->where('action', 'like', '%' . $filters['action'] . '%');
        }

        if ($filters['actor'] !== '' && is_numeric($filters['actor'])) {
            $query->where('actor_user_id', (int) $filters['actor']);
        }

        if ($filters['date_from'] !== '') {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to'] !== '') {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $logs = $query->paginate(40)->withQueryString();

        return view('pages.admin.activity.index', [
            'logs' => $logs,
            'filters' => $filters,
            'meta' => [
                'title' => 'Activity & Audit Log | OmniReferral',
                'description' => 'Review sensitive administrative actions for compliance and troubleshooting.',
            ],
        ]);
    }
}
