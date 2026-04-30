<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AdminAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isStaff(), 403);

        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'role' => (string) $request->query('role', ''),
            'status' => (string) $request->query('status', ''),
            'sort' => (string) $request->query('sort', 'latest'),
        ];

        $query = User::query()->select([
            'id', 'name', 'display_name', 'email', 'role', 'status', 'created_at', 'last_synced_at',
        ]);

        if ($filters['search'] !== '') {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', '%' . $s . '%')
                    ->orWhere('email', 'like', '%' . $s . '%')
                    ->orWhere('display_name', 'like', '%' . $s . '%');
            });
        }

        if ($filters['role'] !== '') {
            $query->where('role', $filters['role']);
        }

        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        match ($filters['sort']) {
            'name' => $query->orderBy('name'),
            'email' => $query->orderBy('email'),
            default => $query->latest(),
        };

        $users = $query->paginate(25)->withQueryString();

        return view('pages.admin.users.index', [
            'users' => $users,
            'filters' => $filters,
            'roles' => ['buyer', 'seller', 'agent', 'admin', 'staff'],
            'statuses' => ['pending', 'active', 'suspended'],
            'canManage' => $request->user()->isAdmin(),
            'isStaffView' => $request->user()->role === 'staff',
            'meta' => [
                'title' => 'User Management | OmniReferral',
                'description' => 'View and manage registered users, roles, and account status across the platform.',
            ],
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot change your own role or status from this screen.');
        }

        if ($user->role === 'admin') {
            return back()->with('error', 'Other administrator accounts cannot be modified here.');
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(['buyer', 'seller', 'agent', 'staff'])],
            'status' => ['required', Rule::in(['pending', 'active', 'suspended'])],
        ]);

        $before = ['role' => $user->role, 'status' => $user->status];
        $user->update([
            'role' => $validated['role'],
            'status' => $validated['status'],
        ]);

        AdminAudit::log($request, 'user.updated', 'user', $user->id, [
            'before' => $before,
            'after' => ['role' => $user->role, 'status' => $user->status],
            'target_email' => $user->email,
        ]);

        return back()->with('success', 'User account updated.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        AdminAudit::log($request, 'users.export.csv', null, null, []);

        $filename = 'users-export-' . now()->format('Ymd-His') . '.csv';
        $query = User::query()->orderBy('id');

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'id', 'name', 'display_name', 'email', 'phone', 'role', 'status', 'staff_team',
                'created_at', 'last_synced_at',
            ]);

            $query->chunkById(500, function ($rows) use ($handle) {
                foreach ($rows as $u) {
                    fputcsv($handle, [
                        $u->id,
                        $u->name,
                        $u->display_name,
                        $u->email,
                        $u->phone,
                        $u->role,
                        $u->status,
                        $u->staff_team,
                        optional($u->created_at)?->toDateTimeString(),
                        optional($u->last_synced_at)?->toDateTimeString(),
                    ]);
                }
            });
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportXlsx(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        AdminAudit::log($request, 'users.export.xlsx', null, null, []);

        $filename = 'users-export-' . now()->format('Ymd-His') . '.xlsx';

        return response()->streamDownload(function () {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray([
                ['ID', 'Name', 'Display name', 'Email', 'Phone', 'Role', 'Status', 'Staff team', 'Joined', 'Last synced'],
            ], null, 'A1');

            $row = 2;
            User::query()->orderByDesc('id')->chunkById(500, function ($users) use ($sheet, &$row) {
                foreach ($users as $u) {
                    $sheet->fromArray([
                        [
                            $u->id,
                            $u->name,
                            $u->display_name,
                            $u->email,
                            $u->phone,
                            $u->role,
                            $u->status,
                            $u->staff_team,
                            optional($u->created_at)?->toDateTimeString(),
                            optional($u->last_synced_at)?->toDateTimeString(),
                        ],
                    ], null, 'A' . $row);
                    $row++;
                }
            });

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
