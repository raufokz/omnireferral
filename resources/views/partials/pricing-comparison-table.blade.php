{{-- Lead package comparison built from active database packages (see PackageComparison) --}}
@php
    /** @var array{headers: array<int, string>, rows: array<int, array<string, mixed>>} $packageComparison */
    $headers = $packageComparison['headers'] ?? [];
    $rows = $packageComparison['rows'] ?? [];
    $colCount = count($headers);
    $y = '<span class="pct-check" aria-hidden="true">✔</span>';
    $dash = '<span class="pct-absent" aria-label="Not included"><span aria-hidden="true">—</span></span>';
@endphp
<section class="section pricing-comparison-section" aria-labelledby="pricing-comparison-heading">
    <div class="container">
        <div class="section-heading" data-animate="up">
            <span class="eyebrow">Compare plans</span>
            <h2 id="pricing-comparison-heading">Lead package comparison</h2>
            <p class="pricing-comparison-intro">Rows reflect your currently active lead packages and their stored features and pricing.</p>
        </div>

        @if ($colCount === 0)
            <div class="pricing-comparison-wrap pricing-comparison-wrap--modern" data-animate="up">
                <p class="pricing-comparison-intro" style="margin:0;">No active lead packages are configured yet. Add packages in the admin area to populate this table.</p>
            </div>
        @else
            <div class="pricing-comparison-wrap pricing-comparison-wrap--modern">
                <table class="pricing-comparison-table pricing-comparison-table--static pricing-comparison-table--modern">
                    <caption class="table-caption-sr">OmniReferral lead package comparison</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="pct-corner"><span class="pct-corner-label">Feature</span></th>
                            @foreach ($headers as $header)
                                @php
                                    $h = (string) $header;
                                    $planName = $h;
                                    $planPrice = '';
                                    if (preg_match('/^(.+)\s+\(\$([0-9,]+)\)\s*$/u', $h, $m)) {
                                        $planName = trim($m[1]);
                                        $planPrice = '$' . $m[2];
                                    }
                                @endphp
                                <th scope="col" class="pct-plan-col">
                                    <span class="pct-plan-name">{{ $planName }}</span>
                                    @if ($planPrice !== '')
                                        <span class="pct-plan-price">{{ $planPrice }}</span>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="pct-tbody">
                        @foreach ($rows as $row)
                            @if (($row['type'] ?? '') === 'group')
                                <tr class="pct-section-row">
                                    <th colspan="{{ $colCount + 1 }}" scope="colgroup" class="pct-section-heading">{{ $row['label'] ?? '' }}</th>
                                </tr>
                            @else
                                <tr>
                                    <th scope="row">{{ $row['feature'] ?? '' }}</th>
                                    @foreach ($row['values'] ?? [] as $cell)
                                        @php
                                            $cell = (string) $cell;
                                        @endphp
                                        <td class="pct-cell @if (! in_array($cell, ['yes', 'no'], true)) pct-cell--text @endif">
                                            @if ($cell === 'yes')
                                                {!! $y !!}
                                            @elseif ($cell === 'no')
                                                {!! $dash !!}
                                            @else
                                                {{ $cell }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>
