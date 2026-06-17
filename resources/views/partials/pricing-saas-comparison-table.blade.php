{{-- Premium SaaS-style comparison table for Starter/Growth/Elite (as requested) --}}
@php
    /** @var string|null $activeSlug */
    $activeSlug = $activeSlug ?? '';
    $isActive = fn($slug) => $activeSlug === $slug;
@endphp

<section class="pricing-saas-comparison" aria-labelledby="pricing-saas-comparison-heading">
    <div class="section-heading" data-animate="up" style="margin-bottom: 14px;">
        <span class="eyebrow">Package Comparison</span>
        <h2 id="pricing-saas-comparison-heading">Starter vs Growth vs Elite</h2>
        <p class="pricing-comparison-intro">Clear side-by-side differences so you can pick the plan that matches your goals.</p>
    </div>

    <div class="pricing-saas-comparison__table-wrap" data-animate="up">
        <table class="pricing-saas-comparison__table">
            <thead>
                <tr>
                    <th class="pct-corner" scope="col">
                        <span class="pct-corner-label">Feature</span>
                    </th>
                    <th scope="col" class="plan-col {{ $isActive('starter-leads') ? 'is-active' : '' }}">
                        <div class="plan-col__name">
                            <span>Starter Lead</span>
                            @if($isActive('starter-leads'))
                                <span class="plan-col__tag plan-col__tag--active">Selected</span>
                            @endif
                        </div>
                        <div class="plan-col__badge plan-col__badge--corner">Starter</div>
                    </th>
                    <th scope="col" class="plan-col plan-col--most-popular {{ $isActive('growth-leads') ? 'is-active' : '' }}">
                        <div class="plan-col__name">
                            <span>Growth Lead</span>
                            <span class="plan-col__tag plan-col__tag--popular">Most Popular</span>
                        </div>
                        <div class="plan-col__badge plan-col__badge--corner">Growth</div>
                    </th>
                    <th scope="col" class="plan-col {{ $isActive('elite-leads') ? 'is-active' : '' }}">
                        <div class="plan-col__name">
                            <span>Elite Lead</span>
                            @if($isActive('elite-leads'))
                                <span class="plan-col__tag plan-col__tag--active">Selected</span>
                            @endif
                        </div>
                        <div class="plan-col__badge plan-col__badge--corner">Elite</div>
                    </th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <th scope="row">Referral Fee</th>
                    <td>15% only on closed deals</td>
                    <td class="is-highlight">7% only on closed deals</td>
                    <td>5% only on closed deals</td>
                </tr>

                <tr>
                    <th scope="row">Free Referrals</th>
                    <td>Included in monthly sourcing</td>
                    <td class="is-highlight">Included in monthly sourcing</td>
                    <td>Included in monthly sourcing</td>
                </tr>

                <tr>
                    <th scope="row">Total Referrals</th>
                    <td>Active buyers & sellers per month</td>
                    <td class="is-highlight">Warm + active opportunities</td>
                    <td>High-intent premium referrals</td>
                </tr>

                <tr>
                    <th scope="row">Cities / Zip Codes</th>
                    <td>Up to 5 cities / ZIPs</td>
                    <td class="is-highlight">Up to 10 cities / ZIPs</td>
                    <td>Up to 15 cities / ZIPs</td>
                </tr>

                <tr>
                    <th scope="row">Support Level</th>
                    <td>Priority support (Call + SMS + Email)</td>
                    <td class="is-highlight">Dedicated senior account manager</td>
                    <td>Dedicated senior wholesaler + go-to account</td>
                </tr>

                <tr>
                    <th scope="row">Virtual Assistance</th>
                    <td>— (campaign managed lane)</td>
                    <td class="is-highlight">— (priority workflow support)</td>
                    <td>Full-time virtual assistant (account manager)</td>
                </tr>

                <tr>
                    <th scope="row">Marketing Support</th>
                    <td>Multi-channel lead generation</td>
                    <td class="is-highlight">Advanced nurturing + profile visibility</td>
                    <td>Expanded exposure + funnels & automation</td>
                </tr>

                <tr>
                    <th scope="row">Verification Level</th>
                    <td>Verified prospects</td>
                    <td class="is-highlight">Higher intent, multi-step verified</td>
                    <td>AI scoring + priority routing</td>
                </tr>

                <tr>
                    <th scope="row">Premium Listings</th>
                    <td>Up to 2 active listings + organic exposure</td>
                    <td class="is-highlight">Exclusive lead flow (selected areas)</td>
                    <td>Unlimited listings + featured placement</td>
                </tr>

                <tr>
                    <th scope="row">Best For</th>
                    <td>Agents who want consistent, verified referral flow</td>
                    <td class="is-highlight">Teams scaling lead intake with strong routing priority</td>
                    <td>Premium teams needing full-team execution + hot lead transfers</td>
                </tr>
            </tbody>
        </table>

        <div class="pricing-saas-comparison__note">
            <span class="pct-check" aria-hidden="true">✔</span>
            <span>Growth Lead is highlighted because it balances cost, coverage, and conversion-focused execution.</span>
        </div>
    </div>
</section>
