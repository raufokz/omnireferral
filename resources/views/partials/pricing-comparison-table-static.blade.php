{{-- OmniReferral pricing comparison (static marketing table) --}}
@php
    $y = '<span class="pct-check" aria-hidden="true">✔</span>';
    $dash = '<span class="pct-absent" aria-label="Not included"><span aria-hidden="true">—</span></span>';
    $m = static fn (int $c): string => str_repeat($y, max(0, $c));
@endphp
<section class="section pricing-comparison-section" aria-labelledby="pricing-comparison-heading">
    <div class="container">
        <div class="section-heading" data-animate="up">
            <span class="eyebrow">Compare plans</span>
            <h2 id="pricing-comparison-heading">Pricing comparison table – OmniReferral</h2>
            <p class="pricing-comparison-intro">Starter, Growth, and Elite at a glance.</p>
        </div>
        <div class="pricing-comparison-wrap pricing-comparison-wrap--modern">
            <table class="pricing-comparison-table pricing-comparison-table--static pricing-comparison-table--modern">
                <caption class="table-caption-sr">OmniReferral pricing: Starter, Growth, and Elite</caption>
                <thead>
                    <tr>
                        <th scope="col" class="pct-corner"><span class="pct-corner-label">Feature</span></th>
                        <th scope="col" class="pct-plan-col">
                            <span class="pct-plan-name">Starter</span>
                            <span class="pct-plan-price">$399/mo</span>
                        </th>
                        <th scope="col" class="pct-plan-col">
                            <span class="pct-plan-name">Growth</span>
                            <span class="pct-plan-price">$899/mo</span>
                        </th>
                        <th scope="col" class="pct-plan-col">
                            <span class="pct-plan-name">Elite</span>
                            <span class="pct-plan-price">$1,999/mo</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="pct-tbody">
                    <tr class="pct-section-row">
                        <th colspan="4" scope="colgroup" class="pct-section-heading">Highlights</th>
                    </tr>
                    <tr>
                        <th scope="row">Qualified Referrals</th>
                        <td class="pct-cell">{!! $m(1) !!}</td>
                        <td class="pct-cell">{!! $m(2) !!}</td>
                        <td class="pct-cell">{!! $m(3) !!}</td>
                    </tr>
                    <tr>
                        <th scope="row">AI + Human Outreach</th>
                        <td class="pct-cell">{!! $m(1) !!}</td>
                        <td class="pct-cell">{!! $m(1) !!}</td>
                        <td class="pct-cell">{!! $m(1) !!}</td>
                    </tr>
                    <tr>
                        <th scope="row">Account Manager</th>
                        <td class="pct-cell">{!! $m(1) !!}</td>
                        <td class="pct-cell pct-cell--text">Senior</td>
                        <td class="pct-cell pct-cell--text">Senior Team + VA</td>
                    </tr>
                    <tr>
                        <th scope="row">Cold Calling ISA</th>
                        <td class="pct-cell">{!! $dash !!}</td>
                        <td class="pct-cell pct-cell--text">1 ISA</td>
                        <td class="pct-cell pct-cell--text">2 ISAs</td>
                    </tr>
                    <tr>
                        <th scope="row">Wholesaler Access</th>
                        <td class="pct-cell">{!! $dash !!}</td>
                        <td class="pct-cell">{!! $m(1) !!}</td>
                        <td class="pct-cell pct-cell--text"><span class="pct-marks">{!! $m(2) !!}</span>Senior</td>
                    </tr>
                    <tr>
                        <th scope="row">JV Deal Opportunities</th>
                        <td class="pct-cell">{!! $dash !!}</td>
                        <td class="pct-cell">{!! $m(1) !!}</td>
                        <td class="pct-cell pct-cell--text"><span class="pct-marks">{!! $m(2) !!}</span>Advanced</td>
                    </tr>
                    <tr>
                        <th scope="row">Territory Coverage</th>
                        <td class="pct-cell pct-cell--text">2 Areas</td>
                        <td class="pct-cell pct-cell--text">5 Areas</td>
                        <td class="pct-cell pct-cell--text">10 Areas</td>
                    </tr>
                    <tr>
                        <th scope="row">Referral Fee</th>
                        <td class="pct-cell pct-cell--text">15%</td>
                        <td class="pct-cell pct-cell--text">7%</td>
                        <td class="pct-cell pct-cell--text">5%</td>
                    </tr>
                    <tr>
                        <th scope="row">Listings on Platform</th>
                        <td class="pct-cell pct-cell--text">2</td>
                        <td class="pct-cell pct-cell--text">Up to 15</td>
                        <td class="pct-cell pct-cell--text">Unlimited</td>
                    </tr>
                    <tr>
                        <th scope="row">Featured Listings</th>
                        <td class="pct-cell">{!! $dash !!}</td>
                        <td class="pct-cell">{!! $m(1) !!}</td>
                        <td class="pct-cell pct-cell--text"><span class="pct-marks">{!! $m(2) !!}</span>Priority</td>
                    </tr>
                </tbody>
                <tbody class="pct-tbody">
                    <tr class="pct-section-row">
                        <th colspan="4" scope="colgroup" class="pct-section-heading">Features</th>
                    </tr>
                    <tr>
                        <th scope="row">Live Call Transfers</th>
                        <td class="pct-cell">{!! $dash !!}</td>
                        <td class="pct-cell">{!! $dash !!}</td>
                        <td class="pct-cell">{!! $m(1) !!}</td>
                    </tr>
                    <tr>
                        <th scope="row">Investor Access</th>
                        <td class="pct-cell">{!! $dash !!}</td>
                        <td class="pct-cell">{!! $m(1) !!}</td>
                        <td class="pct-cell">{!! $m(2) !!}</td>
                    </tr>
                    <tr>
                        <th scope="row">CRM (GHL) Access</th>
                        <td class="pct-cell">{!! $dash !!}</td>
                        <td class="pct-cell">{!! $dash !!}</td>
                        <td class="pct-cell pct-cell--text"><span class="pct-marks">{!! $m(1) !!}</span>Full System</td>
                    </tr>
                    <tr>
                        <th scope="row">Virtual Assistant</th>
                        <td class="pct-cell">{!! $dash !!}</td>
                        <td class="pct-cell">{!! $dash !!}</td>
                        <td class="pct-cell pct-cell--text"><span class="pct-marks">{!! $m(1) !!}</span>Full-Time</td>
                    </tr>
                    <tr>
                        <th scope="row">Support Level</th>
                        <td class="pct-cell pct-cell--text">Email</td>
                        <td class="pct-cell pct-cell--text">Priority</td>
                        <td class="pct-cell pct-cell--text">VIP</td>
                    </tr>
                    <tr>
                        <th scope="row">Strategy Calls</th>
                        <td class="pct-cell pct-cell--text">Monthly</td>
                        <td class="pct-cell pct-cell--text">Weekly</td>
                        <td class="pct-cell pct-cell--text">Weekly + Monthly Planning</td>
                    </tr>
                    <tr>
                        <th scope="row">Performance Tracking</th>
                        <td class="pct-cell pct-cell--text">Basic</td>
                        <td class="pct-cell pct-cell--text">Advanced</td>
                        <td class="pct-cell pct-cell--text">Dashboard + Forecasting</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>
