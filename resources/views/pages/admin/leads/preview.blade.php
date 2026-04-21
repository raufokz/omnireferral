@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Lead Import')
@section('dashboard_title', 'Import Preview')
@section('dashboard_description', 'Review rows before commit. Duplicate records are flagged and skipped.')

@section('dashboard_actions')
    <a href="{{ route('admin.leads.index') }}" class="button button--ghost-blue">Back To Registry</a>
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-grid workspace-grid--3">
        <article class="workspace-card workspace-kpi">
            <span>Total Rows</span>
            <strong>{{ number_format($totalRows) }}</strong>
            <span>Detected from uploaded file</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Ready To Import</span>
            <strong>{{ number_format($newCount) }}</strong>
            <span>Rows that will be inserted</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Duplicates</span>
            <strong>{{ number_format($duplicateCount) }}</strong>
            <span>Rows skipped automatically</span>
        </article>
    </section>

    <section class="workspace-card">
        <div class="workspace-table-wrap">
            <table class="workspace-table">
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
                            $resultLabel = !empty($row['_duplicate']) ? 'Duplicate → Skip' : 'New → Insert';
                            $resultTone = !empty($row['_duplicate']) ? 'rejected' : 'qualified';
                            $priceSummary = !empty($row['budget'])
                                ? 'Budget $' . number_format((int) $row['budget'])
                                : (!empty($row['asking_price']) ? 'Ask $' . number_format((int) $row['asking_price']) : 'No budget or ask');
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $row['name'] ?? '' }}</strong>
                                <div class="workspace-property__meta">{{ $row['email'] ?? '' }} · {{ $row['phone'] ?? '' }}</div>
                            </td>
                            <td>
                                <span class="status-pill status-pill--{{ $statusTone }}">{{ $statusLabel }}</span>
                                <div class="workspace-property__meta">{{ ucfirst($row['intent'] ?? 'buyer') }}</div>
                            </td>
                            <td>
                                <strong>{{ $row['property_address'] ?? 'No address provided' }}</strong>
                                <div class="workspace-property__meta">{{ $row['zip_code'] ?? '' }} · {{ $row['state'] ?? 'State N/A' }}</div>
                                <div class="workspace-property__meta">{{ $priceSummary }}</div>
                            </td>
                            <td>
                                <div class="workspace-property__meta">Rep: {{ $row['rep_name'] ?? 'N/A' }}</div>
                                <div class="workspace-property__meta">Sent to: {{ $row['sent_to'] ?? 'N/A' }}</div>
                                <div class="workspace-property__meta">Assignment: {{ $row['assignment'] ?? 'Unassigned on import' }}</div>
                            </td>
                            <td>
                                <span class="status-pill status-pill--{{ $resultTone }}">{{ $resultLabel }}</span>
                                @if(!empty($row['_duplicate_reason']))
                                    <div class="workspace-property__meta">{{ $row['_duplicate_reason'] }}</div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($totalRows > count($rows))
            <p class="workspace-property__meta" style="margin-top: 0.75rem;">Showing first {{ count($rows) }} rows in preview. Commit still processes all {{ $totalRows }} rows.</p>
        @endif
    </section>

    <section class="workspace-actions">
        <form action="{{ route('admin.leads.import.commit') }}" method="POST" class="js-loading-form">
            @csrf
            <input type="hidden" name="preview_key" value="{{ $key }}">
            <button type="submit" class="button js-loading-btn">Confirm Import</button>
        </form>
        <a href="{{ route('admin.leads.index') }}" class="button button--ghost-blue">Cancel</a>
    </section>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-loading-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const button = event.submitter || form.querySelector('.js-loading-btn');
            if (button) {
                button.disabled = true;
            }
        });
    });
});
</script>
@endpush
@endsection
