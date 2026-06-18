@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'GHL Field Mappings')
@section('dashboard_description', 'Map GoHighLevel form fields to OmniReferral database columns for buyers, sellers, and agents.')

@section('dashboard_actions')
    <a href="{{ route('admin.ghl.index') }}" class="button button--ghost-blue">Overview</a>
    <a href="{{ route('admin.ghl.settings') }}" class="button button--ghost-blue">Settings</a>
@endsection

@section('content')
<div class="workspace-stack">

    @if(session('success'))
        <div class="form-alert form-alert--success">{{ session('success') }}</div>
    @endif

    @php $canEdit = auth()->user()?->isSuperAdmin(); @endphp

    @unless($canEdit)
        <div class="workspace-card" style="border-left:4px solid var(--color-warning,#f59e0b);">
            <strong>View-only mode.</strong> Only super admins can add, edit, or remove mappings.
        </div>
    @endunless

    {{-- Add new mapping --}}
    @if($canEdit)
    <section class="workspace-card">
        <span class="eyebrow">Add Mapping</span>
        <h2>New field mapping</h2>
        <form action="{{ route('admin.ghl.mappings.add') }}" method="POST" class="workspace-form-grid" style="align-items:end;">
            @csrf
            <label class="workspace-field">
                <span>GHL Field Name</span>
                <input type="text" name="ghl_field" required placeholder="e.g. license_number" value="{{ old('ghl_field') }}">
            </label>
            <label class="workspace-field">
                <span>DB Table</span>
                <select name="db_table" required>
                    @foreach($supportedTables as $tbl)
                        <option value="{{ $tbl }}" {{ old('db_table') === $tbl ? 'selected' : '' }}>{{ $tbl }}</option>
                    @endforeach
                </select>
            </label>
            <label class="workspace-field">
                <span>DB Column</span>
                <input type="text" name="db_column" required placeholder="e.g. license_number" value="{{ old('db_column') }}">
            </label>
            <label class="workspace-field">
                <span>Label <em style="color:var(--color-text-muted,#9ca3af);">(optional)</em></span>
                <input type="text" name="label" placeholder="Human-readable label" value="{{ old('label') }}">
            </label>
            <div class="workspace-field">
                <span>&nbsp;</span>
                <button type="submit" class="button button--orange">Add Mapping</button>
            </div>
        </form>
    </section>
    @endif

    {{-- Existing mappings grouped by table --}}
    @foreach($supportedTables as $table)
        @php $tableMappings = $mappings->where('db_table', $table); @endphp
        <section class="workspace-card">
            <span class="eyebrow">{{ $table }}</span>
            <h2>{{ match($table) { 'users' => 'Users table', 'realtor_profiles' => 'Realtor profiles', 'buyer_profiles' => 'Buyer profiles', default => $table } }}</h2>

            @if($tableMappings->isEmpty())
                <div class="workspace-empty">No mappings configured for this table.</div>
            @else
            <div class="workspace-table-wrap" style="margin-top:.75rem;">
                <table class="workspace-table">
                    <thead>
                        <tr>
                            <th>GHL Field</th>
                            <th>→</th>
                            <th>DB Column</th>
                            <th>Label</th>
                            <th>Active</th>
                            <th>Order</th>
                            @if($canEdit)<th></th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tableMappings as $mapping)
                        <tr>
                            <td><code>{{ $mapping->ghl_field }}</code></td>
                            <td style="color:var(--color-text-muted,#6b7280);">→</td>
                            <td><code>{{ $mapping->db_column }}</code></td>
                            <td>{{ $mapping->label ?: '—' }}</td>
                            <td>
                                <span class="workspace-pill {{ $mapping->is_active ? 'workspace-pill--green' : 'workspace-pill--grey' }}">
                                    {{ $mapping->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $mapping->sort_order }}</td>
                            @if($canEdit)
                            <td style="display:flex; gap:.5rem; flex-wrap:wrap;">
                                <form action="{{ route('admin.ghl.mappings.toggle', $mapping) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="button button--ghost-blue" style="font-size:.78rem; padding:.25rem .6rem;">
                                        {{ $mapping->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.ghl.mappings.delete', $mapping) }}" method="POST"
                                    onsubmit="return confirm('Remove this mapping?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button" style="font-size:.78rem; padding:.25rem .6rem; background:var(--color-danger,#ef4444); color:#fff; border-color:transparent;">Remove</button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </section>
    @endforeach

</div>
@endsection
