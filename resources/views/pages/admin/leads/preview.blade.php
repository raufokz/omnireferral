@extends('layouts.app')

@section('content')
<section class="section dashboard-page lead-ops-page">
    <div class="container">
        <div class="lead-ops-header">
            <div>
                <span class="eyebrow">Lead Import</span>
                <h1>Preview Before Import</h1>
                <p>Qualified rows show in green, rejected rows show in red, and duplicate email or phone matches are skipped before insert.</p>
            </div>
            <div class="lead-ops-header__actions">
                <a href="{{ route('admin.leads.index') }}" class="button button--ghost-blue">Back To Lead Ops</a>
            </div>
        </div>

        <div class="lead-ops-stats-grid">
            <div class="cockpit-table-card lead-ops-stat-card">
                <strong>{{ $totalRows }}</strong>
                <span>Total rows detected</span>
            </div>
            <div class="cockpit-table-card lead-ops-stat-card">
                <strong>{{ $newCount }}</strong>
                <span>Rows ready to import</span>
            </div>
            <div class="cockpit-table-card lead-ops-stat-card">
                <strong>{{ $duplicateCount }}</strong>
                <span>Duplicates to skip</span>
            </div>
        </div>

        <div class="cockpit-table-card lead-ops-table-wrap">
            <table class="cockpit-table lead-ops-table">
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Status</th>
                        <th>Market</th>
                        <th>Ops Data</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        @php
                            $status = $row['status'] ?? 'new';
                            $statusTone = $status === 'qualified' ? 'qualified' : ($status === 'not_interested' ? 'rejected' : $status);
                            $statusLabel = $status === 'not_interested' ? 'Rejected' : ucfirst(str_replace('_', ' ', $status));
                            $resultLabel = !empty($row['_duplicate']) ? 'Duplicate -> Skip' : 'New -> Insert';
                            $resultTone = !empty($row['_duplicate']) ? 'rejected' : 'qualified';
                            $priceSummary = !empty($row['budget'])
                                ? 'Budget $' . number_format((int) $row['budget'])
                                : (!empty($row['asking_price']) ? 'Ask $' . number_format((int) $row['asking_price']) : 'No budget or ask');
                        @endphp
                        <tr>
                            <td>
                                <div class="lead-ops-cell-stack">
                                    <strong>{{ $row['name'] ?? '' }}</strong>
                                    <span class="cockpit-secondary-data">{{ $row['email'] ?? '' }} | {{ $row['phone'] ?? '' }}</span>
                                    <span class="cockpit-secondary-data">{{ optional($row['source_timestamp'] ?? null)->format('M d, Y g:i A') ?: 'No timestamp' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="lead-ops-cell-stack">
                                    <span class="status-pill status-pill--{{ $statusTone }}">{{ $statusLabel }}</span>
                                    <span class="cockpit-secondary-data">{{ ucfirst($row['intent'] ?? 'buyer') }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="lead-ops-cell-stack">
                                    <strong>{{ $row['property_address'] ?? 'No address provided' }}</strong>
                                    <span class="cockpit-secondary-data">{{ $row['zip_code'] ?? '' }} | {{ $row['state'] ?? 'State N/A' }}</span>
                                    <span class="cockpit-secondary-data">{{ $row['beds_baths'] ?? 'Beds/Baths N/A' }} | {{ $priceSummary }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="lead-ops-cell-stack">
                                    <span class="cockpit-secondary-data">Rep: {{ $row['rep_name'] ?? 'N/A' }}</span>
                                    <span class="cockpit-secondary-data">Sent to: {{ $row['sent_to'] ?? 'N/A' }}</span>
                                    <span class="cockpit-secondary-data">Assignment: {{ $row['assignment'] ?? 'Unassigned on import' }}</span>
                                    <span class="cockpit-secondary-data">Reason: {{ $row['reason_in_house'] ?? 'N/A' }}</span>
                                    <span class="cockpit-secondary-data">Response: {{ $row['realtor_response'] ?? 'Pending' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="lead-ops-cell-stack">
                                    <span class="status-pill status-pill--{{ $resultTone }}">{{ $resultLabel }}</span>
                                    @if(!empty($row['_duplicate_reason']))
                                        <span class="cockpit-secondary-data">{{ $row['_duplicate_reason'] }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($totalRows > count($rows))
                <p class="lead-ops-preview-note">Showing the first {{ count($rows) }} rows in preview. The full import will still process all {{ $totalRows }} rows.</p>
            @endif
        </div>

        <div class="lead-ops-preview-actions">
            <form action="{{ route('admin.leads.import.commit') }}" method="POST" class="js-loading-form" data-loading-scope="lead-ops">
                @csrf
                <input type="hidden" name="preview_key" value="{{ $key }}">
                <button type="submit" class="button button--orange js-loading-btn" data-loading-text="Importing Leads...">Confirm Import</button>
            </form>
            <a href="{{ route('admin.leads.index') }}" class="button button--ghost-blue">Cancel</a>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-loading-form').forEach(function (form) {
        form.addEventListener('submit', function () {
            const btn = form.querySelector('.js-loading-btn');

            if (btn) {
                btn.disabled = true;
                btn.textContent = btn.dataset.loadingText || 'Processing...';
            }
        });
    });
});
</script>
@endpush
@endsection
