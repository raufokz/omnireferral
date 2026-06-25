@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'SEO Landing Pages')
@section('dashboard_title', 'Edit: ' . $page->city . ', ' . $page->state)
@section('dashboard_description', 'Edit content, SEO settings, and page sections.')

@section('dashboard_actions')
    <a href="{{ route('admin.seo-landing-pages.index') }}" class="button button--ghost-blue">Back to List</a>
    <a href="{{ route('seo-landing-page.show', $page->slug) }}" target="_blank" class="button">View Live</a>
@endsection

@section('content')
@php
    $assignedProfile = $page->realtorProfile;
    $assignedUser = $assignedProfile?->user;
@endphp

<section class="workspace-card">
    <form method="POST" action="{{ route('admin.seo-landing-pages.update', $page) }}">
        @csrf
        @method('PUT')

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2rem;">
            <label class="workspace-field">
                <span>City</span>
                <input type="text" name="city" value="{{ old('city', $page->city) }}" required>
            </label>
            <label class="workspace-field">
                <span>State (2-letter code)</span>
                <input type="text" name="state" value="{{ old('state', $page->state) }}" maxlength="2" required>
            </label>
            <label class="workspace-field">
                <span>Primary Keyword</span>
                <input type="text" name="primary_keyword" value="{{ old('primary_keyword', $page->primary_keyword) }}" required>
            </label>
            <label class="workspace-field">
                <span>Assigned Realtor Profile</span>
                <select name="realtor_profile_id">
                    <option value="">Use generic page content</option>
                    @foreach($realtorProfiles as $profile)
                        @php
                            $label = trim(($profile->user?->publicDisplayName() ?? 'Unnamed realtor') . ' - ' . ($profile->serviceAreaLabel() ?: $profile->slug));
                        @endphp
                        <option value="{{ $profile->id }}" @selected((string) old('realtor_profile_id', $page->realtor_profile_id) === (string) $profile->id)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @if($page->realtorProfile)
                    <small>
                        <a href="{{ route('admin.agent-profiles.show', $page->realtorProfile) }}" target="_blank">Edit assigned realtor profile</a>
                    </small>
                @endif
            </label>
            <label class="workspace-field">
                <span>SEO Title</span>
                <input type="text" name="seo_title" value="{{ old('seo_title', $page->seo_title) }}">
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Meta Description</span>
                <textarea name="meta_description" rows="3">{{ old('meta_description', $page->meta_description) }}</textarea>
            </label>
            <label class="workspace-field">
                <span>Canonical URL</span>
                <input type="url" name="canonical_url" value="{{ old('canonical_url', $page->canonical_url) }}" placeholder="Leave blank to use current URL">
            </label>
            <label class="workspace-field">
                <span>Hero Image URL</span>
                <input type="text" name="hero_image" value="{{ old('hero_image', $page->hero_image) }}" placeholder="e.g. images/seo/austin-hero.jpg">
            </label>
            <label class="workspace-field">
                <span>OG Image URL</span>
                <input type="text" name="og_image" value="{{ old('og_image', $page->og_image) }}" placeholder="e.g. images/seo/og-austin.jpg">
            </label>
            <label class="workspace-field">
                <span>Secondary Keywords (one per line)</span>
                <textarea name="secondary_keywords" rows="5">{{ old('secondary_keywords', is_array($page->secondary_keywords) ? implode("\n", $page->secondary_keywords) : $page->secondary_keywords) }}</textarea>
            </label>
            <label class="workspace-field">
                <span>Publish Status</span>
                <select name="is_published">
                    <option value="0" @selected(!$page->is_published)>Draft</option>
                    <option value="1" @selected($page->is_published)>Published</option>
                </select>
            </label>
        </div>

        <div style="margin-bottom:2rem; padding:1rem; border:1px solid rgba(11,54,104,.12); border-radius:8px; background:#f8fafc;">
            <div style="display:flex; justify-content:space-between; gap:1rem; align-items:flex-start; flex-wrap:wrap;">
                <div style="display:flex; gap:1rem; align-items:center; min-width:0;">
                    <img
                        src="{{ $assignedProfile ? $assignedProfile->headshotPublicUrl($assignedUser) : asset('images/realtors/logo-bydefault_agent.png') }}"
                        alt="{{ $assignedUser?->publicDisplayName() ?? 'Assigned realtor' }} headshot"
                        width="76"
                        height="76"
                        style="width:76px; height:76px; object-fit:cover; border-radius:8px; border:1px solid rgba(11,54,104,.14);"
                    >
                    <div style="min-width:0;">
                        <span class="eyebrow">Assigned Realtor Profile</span>
                        <h3 style="margin:.15rem 0 .25rem; font-size:1.15rem;">{{ $assignedUser?->publicDisplayName() ?? 'No realtor assigned' }}</h3>
                        @if($assignedProfile)
                            <p style="margin:0; color:#64748b;">
                                {{ $assignedProfile->brokerage_name ?: 'Brokerage not set' }}
                                @if($assignedProfile->serviceAreaLabel())
                                    <span>&middot;</span> {{ $assignedProfile->serviceAreaLabel() }}
                                @endif
                            </p>
                            <p style="margin:.35rem 0 0; color:#64748b;">
                                {{ $assignedUser?->email ?? 'Email not set' }}
                                @if($assignedUser?->phone)
                                    <span>&middot;</span> {{ $assignedUser->phone }}
                                @endif
                            </p>
                        @else
                            <p style="margin:0; color:#64748b;">Select one realtor above, save changes, then edit their full profile details from the admin profile screen.</p>
                        @endif
                    </div>
                </div>
                <div class="workspace-actions">
                    <a href="{{ route('admin.agent-profiles.index') }}" class="button button--ghost-blue">Choose From Profiles</a>
                    @if($assignedProfile)
                        <a href="{{ route('admin.agent-profiles.show', $assignedProfile) }}" class="button button--orange" target="_blank">Edit Image, Name, Email & Phone</a>
                    @endif
                </div>
            </div>
        </div>

        <hr style="margin:2rem 0; border:none; border-top:1px solid #eee;">

        <h3 style="margin-bottom:1.5rem;">Page Content Sections</h3>

        @php $c = $page->content ?? []; @endphp

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
            <label class="workspace-field">
                <span>Hero Heading</span>
                <input type="text" name="content[hero_heading]" value="{{ old('content.hero_heading', $c['hero_heading'] ?? '') }}">
            </label>
            <label class="workspace-field">
                <span>Hero Subheading</span>
                <input type="text" name="content[hero_subheading]" value="{{ old('content.hero_subheading', $c['hero_subheading'] ?? '') }}">
            </label>
            <label class="workspace-field">
                <span>Agent Name</span>
                <input type="text" name="content[agent_name]" value="{{ old('content.agent_name', $c['agent_name'] ?? '') }}">
            </label>
            <label class="workspace-field">
                <span>Agent Title</span>
                <input type="text" name="content[agent_title]" value="{{ old('content.agent_title', $c['agent_title'] ?? '') }}">
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Agent Bio</span>
                <textarea name="content[agent_bio]" rows="6">{{ old('content.agent_bio', $c['agent_bio'] ?? '') }}</textarea>
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Why Work With a Local Realtor (heading)</span>
                <input type="text" name="content[why_local_heading]" value="{{ old('content.why_local_heading', $c['why_local_heading'] ?? '') }}">
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Why Work With a Local Realtor (content)</span>
                <textarea name="content[why_local_content]" rows="6">{{ old('content.why_local_content', $c['why_local_content'] ?? '') }}</textarea>
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Home Buying heading</span>
                <input type="text" name="content[buying_heading]" value="{{ old('content.buying_heading', $c['buying_heading'] ?? '') }}">
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Home Buying content</span>
                <textarea name="content[buying_content]" rows="6">{{ old('content.buying_content', $c['buying_content'] ?? '') }}</textarea>
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Home Selling heading</span>
                <input type="text" name="content[selling_heading]" value="{{ old('content.selling_heading', $c['selling_heading'] ?? '') }}">
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Home Selling content</span>
                <textarea name="content[selling_content]" rows="6">{{ old('content.selling_content', $c['selling_content'] ?? '') }}</textarea>
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Relocation heading</span>
                <input type="text" name="content[relocation_heading]" value="{{ old('content.relocation_heading', $c['relocation_heading'] ?? '') }}">
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Relocation content</span>
                <textarea name="content[relocation_content]" rows="6">{{ old('content.relocation_content', $c['relocation_content'] ?? '') }}</textarea>
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Luxury heading</span>
                <input type="text" name="content[luxury_heading]" value="{{ old('content.luxury_heading', $c['luxury_heading'] ?? '') }}">
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Luxury content</span>
                <textarea name="content[luxury_content]" rows="6">{{ old('content.luxury_content', $c['luxury_content'] ?? '') }}</textarea>
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Investment heading</span>
                <input type="text" name="content[investment_heading]" value="{{ old('content.investment_heading', $c['investment_heading'] ?? '') }}">
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Investment content</span>
                <textarea name="content[investment_content]" rows="6">{{ old('content.investment_content', $c['investment_content'] ?? '') }}</textarea>
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Local Market heading</span>
                <input type="text" name="content[market_heading]" value="{{ old('content.market_heading', $c['market_heading'] ?? '') }}">
            </label>
            <label class="workspace-field workspace-field--full">
                <span>Local Market content</span>
                <textarea name="content[market_content]" rows="6">{{ old('content.market_content', $c['market_content'] ?? '') }}</textarea>
            </label>
            <label class="workspace-field workspace-field--full">
                <span>CTA Heading</span>
                <input type="text" name="content[cta_heading]" value="{{ old('content.cta_heading', $c['cta_heading'] ?? '') }}">
            </label>
            <label class="workspace-field workspace-field--full">
                <span>CTA Subheading</span>
                <input type="text" name="content[cta_subheading]" value="{{ old('content.cta_subheading', $c['cta_subheading'] ?? '') }}">
            </label>
            <label class="workspace-field">
                <span>Form Heading</span>
                <input type="text" name="content[form_heading]" value="{{ old('content.form_heading', $c['form_heading'] ?? '') }}">
            </label>
            <label class="workspace-field">
                <span>Form Subheading</span>
                <input type="text" name="content[form_subheading]" value="{{ old('content.form_subheading', $c['form_subheading'] ?? '') }}">
            </label>
            <label class="workspace-field">
                <span>Form Submit Button Text</span>
                <input type="text" name="content[form_submit_text]" value="{{ old('content.form_submit_text', $c['form_submit_text'] ?? '') }}">
            </label>
        </div>

        <hr style="margin:2rem 0; border:none; border-top:1px solid #eee;">

        <h3 style="margin-bottom:1rem;">Service Areas</h3>
        <p style="font-size:.85rem; color:#666; margin-bottom:1rem;">Enter one area per line.</p>
        <label class="workspace-field workspace-field--full">
            <textarea name="content[service_areas]" rows="5">{{ old('content.service_areas', is_array($c['service_areas'] ?? null) ? implode("\n", $c['service_areas']) : ($c['service_areas'] ?? '')) }}</textarea>
        </label>

        <hr style="margin:2rem 0; border:none; border-top:1px solid #eee;">

        <h3 style="margin-bottom:1rem;">FAQs</h3>
        @php $faqs = $c['faqs'] ?? []; @endphp
        <div id="faqs-container">
            @foreach($faqs as $i => $faq)
            <div class="faq-item" style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem; padding:1rem; background:#f8fafc; border-radius:8px;">
                <label class="workspace-field">
                    <span>Question</span>
                    <input type="text" name="content[faqs][{{ $i }}][question]" value="{{ $faq['question'] }}">
                </label>
                <label class="workspace-field">
                    <span>Answer</span>
                    <textarea name="content[faqs][{{ $i }}][answer]" rows="3">{{ $faq['answer'] }}</textarea>
                </label>
            </div>
            @endforeach
        </div>
        <button type="button" onclick="addFaq()" class="button button--ghost-blue" style="margin-top:.5rem;">+ Add FAQ</button>

        <div class="workspace-actions" style="margin-top:2rem;">
            <button type="submit" class="button">Save Changes</button>
            <a href="{{ route('admin.seo-landing-pages.index') }}" class="button button--ghost-blue">Cancel</a>
        </div>
    </form>
</section>
@endsection

@push('scripts')
<script>
let faqIndex = {{ count($c['faqs'] ?? []) }};
function addFaq() {
    const container = document.getElementById('faqs-container');
    const div = document.createElement('div');
    div.className = 'faq-item';
    div.style.cssText = 'display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem; padding:1rem; background:#f8fafc; border-radius:8px;';
    div.innerHTML = `
        <label class="workspace-field">
            <span>Question</span>
            <input type="text" name="content[faqs][${faqIndex}][question]" placeholder="Enter question">
        </label>
        <label class="workspace-field">
            <span>Answer</span>
            <textarea name="content[faqs][${faqIndex}][answer]" rows="3" placeholder="Enter answer"></textarea>
        </label>
    `;
    container.appendChild(div);
    faqIndex++;
}
</script>
@endpush
