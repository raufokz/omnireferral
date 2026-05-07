<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\Package;
use App\Models\User;
use App\Support\AdminAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'role' => (string) $request->query('role', ''),
            'status' => (string) $request->query('status', ''),
            'date_from' => (string) $request->query('date_from', ''),
            'date_to' => (string) $request->query('date_to', ''),
            'sort' => (string) $request->query('sort', 'latest'),
        ];

        $query = User::query();

        if ($filters['search'] !== '') {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', '%'.$s.'%')
                    ->orWhere('email', 'like', '%'.$s.'%')
                    ->orWhere('display_name', 'like', '%'.$s.'%')
                    ->orWhere('phone', 'like', '%'.$s.'%')
                    ->orWhere('affiliate_code', 'like', '%'.$s.'%');
            });
        }

        if ($filters['role'] !== '') {
            $query->where('role', $filters['role']);
        }

        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if ($filters['date_from'] !== '' && strtotime($filters['date_from']) !== false) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to'] !== '' && strtotime($filters['date_to']) !== false) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        match ($filters['sort']) {
            'name' => $query->orderBy('name'),
            'email' => $query->orderBy('email'),
            'oldest' => $query->oldest(),
            'updated' => $query->latest('updated_at'),
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

    public function show(Request $request, User $user): View
    {
        $this->authorize('view', $user);

        $user->load([
            'referrer',
            'currentPlan',
            'realtorProfile',
            'affiliateProfile',
        ])->loadCount([
            'ownedProperties',
            'listedProperties',
            'receivedContacts',
            'referrals',
            'enquiriesReceived',
            'enquiriesSent',
        ]);

        $recentEnquiriesIn = Enquiry::query()
            ->with(['property:id,title,slug', 'sender'])
            ->where('receiver_user_id', $user->id)
            ->latest()
            ->limit(8)
            ->get();

        $recentListings = $user->listedProperties()
            ->select(['id', 'title', 'slug', 'status', 'created_at'])
            ->latest()
            ->limit(6)
            ->get();

        return view('pages.admin.users.show', [
            'record' => $user,
            'recentEnquiriesIn' => $recentEnquiriesIn,
            'recentListings' => $recentListings,
            'canEdit' => $request->user()->isAdmin(),
            'canSuspend' => $request->user()->isAdmin() && $user->id !== $request->user()->id,
            'meta' => [
                'title' => $user->publicDisplayName().' — User record | OmniReferral',
                'description' => 'Full account profile, relationships, and activity summary.',
            ],
        ]);
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorize('update', $user);

        if ($user->id === $request->user()->id) {
            return redirect()
                ->route('account.profile')
                ->with('info', 'Use your profile page to edit your own account.');
        }

        $user->load(['referrer', 'currentPlan']);

        $plans = Package::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'category']);

        return view('pages.admin.users.edit', [
            'record' => $user,
            'plans' => $plans,
            'staffTeams' => [
                'isa' => 'ISA',
                'sales' => 'Sales',
                'marketing' => 'Marketing',
                'web_dev' => 'Web development',
            ],
            'meta' => [
                'title' => 'Edit '.$user->publicDisplayName().' | OmniReferral',
                'description' => 'Update user profile, security flags, and subscription.',
            ],
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Edit your own account from Profile settings.');
        }

        $emailBefore = $user->email;

        $request->merge([
            'referred_by_user_id' => $request->filled('referred_by_user_id') ? (int) $request->input('referred_by_user_id') : null,
            'current_plan_id' => $request->filled('current_plan_id') ? (int) $request->input('current_plan_id') : null,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:120'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'social_facebook_url' => ['nullable', 'url', 'max:255'],
            'social_linkedin_url' => ['nullable', 'url', 'max:255'],
            'role' => ['required', Rule::in(['buyer', 'seller', 'agent', 'admin', 'staff'])],
            'staff_team' => ['nullable', Rule::in(['isa', 'sales', 'marketing', 'web_dev'])],
            'status' => ['required', Rule::in(['pending', 'active', 'suspended'])],
            'current_plan_id' => ['nullable', 'integer', 'exists:packages,id'],
            'referred_by_user_id' => ['nullable', 'integer', 'exists:users,id', Rule::notIn([$user->id])],
            'affiliate_code' => ['nullable', 'string', 'max:64'],
            'notify_email' => ['nullable', Rule::in(['0', '1'])],
            'notify_marketing' => ['nullable', Rule::in(['0', '1'])],
            'two_factor_enabled' => ['nullable', Rule::in(['0', '1'])],
            'must_reset_password' => ['nullable', Rule::in(['0', '1'])],
            'email_verified' => ['nullable', Rule::in(['0', '1'])],
            'avatar' => ['nullable', 'image', 'max:4096'],
            'remove_avatar' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $this->assertSafeRoleChange($request->user(), $user, $validated['role'], $validated['status']);

        $payload = [
            'name' => $validated['name'],
            'display_name' => $validated['display_name'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address_line_1' => $validated['address_line_1'] ?? null,
            'address_line_2' => $validated['address_line_2'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'zip_code' => $validated['zip_code'] ?? null,
            'social_facebook_url' => $validated['social_facebook_url'] ?? null,
            'social_linkedin_url' => $validated['social_linkedin_url'] ?? null,
            'role' => $validated['role'],
            'staff_team' => $validated['role'] === 'staff' ? ($validated['staff_team'] ?? null) : null,
            'status' => $validated['status'],
            'current_plan_id' => $validated['current_plan_id'] ?? null,
            'referred_by_user_id' => $validated['referred_by_user_id'] ?? null,
            'affiliate_code' => $validated['affiliate_code'] ?? null,
            'notify_email' => ($validated['notify_email'] ?? '1') === '1',
            'notify_marketing' => ($validated['notify_marketing'] ?? '0') === '1',
            'two_factor_enabled' => ($validated['two_factor_enabled'] ?? '0') === '1',
            'must_reset_password' => ($validated['must_reset_password'] ?? '0') === '1',
        ];

        if ($request->hasFile('avatar')) {
            $this->deleteStoredAvatarFile($user->avatar);
            $payload['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } elseif (! empty($validated['remove_avatar'])) {
            $this->deleteStoredAvatarFile($user->avatar);
            $payload['avatar'] = null;
        }

        if (! empty($validated['password'])) {
            $payload['password'] = $validated['password'];
            $payload['password_set_at'] = now();
            $payload['must_reset_password'] = false;
        }

        $user->fill($payload);

        $emailChanged = $emailBefore !== $validated['email'];
        if (($validated['email_verified'] ?? '1') === '1') {
            $user->email_verified_at = $emailChanged ? now() : ($user->email_verified_at ?? now());
        } else {
            $user->email_verified_at = null;
        }

        $user->save();

        AdminAudit::log($request, 'user.profile.updated', 'user', $user->id, [
            'target_email' => $user->email,
            'changed_email' => $emailChanged,
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User account updated.');
    }

    public function quickUpdate(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

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

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        if ($user->role === 'admin') {
            $adminCount = User::query()->where('role', 'admin')->where('status', '!=', 'suspended')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Cannot deactivate the last active administrator.');
            }
        }

        $user->update(['status' => 'suspended']);

        AdminAudit::log($request, 'user.deactivated', 'user', $user->id, [
            'target_email' => $user->email,
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User has been deactivated (suspended).');
    }

    protected function assertSafeRoleChange(User $actor, User $target, string $newRole, string $newStatus): void
    {
        if ($target->id === $actor->id && ($newRole !== $actor->role || $newStatus === 'suspended')) {
            abort(422, 'Invalid change to your own account.');
        }

        if ($target->role === 'admin' && $newRole !== 'admin') {
            $remaining = User::query()
                ->where('role', 'admin')
                ->where('status', 'active')
                ->whereKeyNot($target->id)
                ->count();
            if ($remaining < 1) {
                abort(422, 'Cannot remove the last administrator.');
            }
        }

        if ($target->role === 'admin' && $newStatus === 'suspended') {
            $remaining = User::query()
                ->where('role', 'admin')
                ->where('status', 'active')
                ->whereKeyNot($target->id)
                ->count();
            if ($remaining < 1) {
                abort(422, 'Cannot suspend the last active administrator.');
            }
        }
    }

    protected function deleteStoredAvatarFile(?string $path): void
    {
        if (! is_string($path) || $path === '') {
            return;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/storage/', 'storage/', 'images/'])) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $this->authorize('export', User::class);

        AdminAudit::log($request, 'users.export.csv', null, null, []);

        $filename = 'users-export-'.now()->format('Ymd-His').'.csv';
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
        $this->authorize('export', User::class);

        AdminAudit::log($request, 'users.export.xlsx', null, null, []);

        $filename = 'users-export-'.now()->format('Ymd-His').'.xlsx';

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
                    ], null, 'A'.$row);
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
