@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'SEO Landing Pages')
@section('dashboard_title', 'Edit: ' . $page->city . ', ' . $page->state)
@section('dashboard_description', 'Edit content, SEO settings, and page sections.')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<style>
.quill-wrap { margin-bottom:1.5rem; }
.quill-wrap label { display:block; font-size:.85rem; font-weight:600; color:#334155; margin-bottom:.4rem; }
.quill-wrap .ql-container { min-height:150px; font-size:.95rem; }
.ql-editor { min-height:150px; }
</style>
@endpush

@section('dashboard_actions')
    <a href="{{ route('admin.seo-landing-pages.index') }}" class="button button--ghost-blue">Back to List</a>
    <a href="{{ route('seo-landing-page.show', $page->slug) }}" target="_blank" class="button">View Live</a>
@endsection

@section('content')
@php
    $assignedProfile = $page->realtorProfile;
    $assignedUser = $assignedProfile?->user;
    $c = $page->content ?? [];
@endphp

<section class="workspace-card">
    <form method="POST" action="{{ route('admin.seo-landing-pages.update', $page) }}" id="seo-form" enctype="multipart/form-data">
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
                <span>Slug</span>
                <input type="text" name="slug" value="{{ old('slug', $page->slug) }}">
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
                <span>Hero Image</span>
                <input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp">
                @if($page->hero_image)
                    <div style="margin-top:.5rem; display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;">
                        <img src="{{ asset($page->hero_image) }}" alt="Hero preview" style="width:120px; height:auto; border-radius:6px; border:1px solid #e2e8f0;">
                        <label style="font-size:.8rem; display:flex; align-items:center; gap:.35rem; cursor:pointer;">
                            <input type="checkbox" name="hero_image_remove" value="1">
                            Remove current image
                        </label>
                    </div>
                @endif
                <small style="color:#64748b;">Upload hero backdrop (JPEG, PNG, WebP, max 5MB). Leave empty to keep current.</small>
            </label>
            <label class="workspace-field">
                <span>Realtor Photo</span>
                <input type="file" name="realtor_photo" accept="image/jpeg,image/png,image/webp">
                @if($page->realtor_photo)
                    <div style="margin-top:.5rem; display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;">
                        <img src="{{ asset($page->realtor_photo) }}" alt="Realtor photo preview" style="width:76px; height:76px; object-fit:cover; border-radius:8px; border:1px solid #e2e8f0;">
                        <label style="font-size:.8rem; display:flex; align-items:center; gap:.35rem; cursor:pointer;">
                            <input type="checkbox" name="realtor_photo_remove" value="1">
                            Remove current photo
                        </label>
                    </div>
                @endif
                <small style="color:#64748b;">Upload realtor headshot (JPEG, PNG, WebP, max 5MB). Leave empty to keep current.</small>
            </label>
            <label class="workspace-field">
                <span>Agent Info Image</span>
                <input type="file" name="agent_info_image" accept="image/jpeg,image/png,image/webp">
                @if($page->agent_info_image)
                    <div style="margin-top:.5rem; display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;">
                        <img src="{{ asset($page->agent_info_image) }}" alt="Agent info image preview" style="width:76px; height:76px; object-fit:cover; border-radius:8px; border:1px solid #e2e8f0;">
                        <label style="font-size:.8rem; display:flex; align-items:center; gap:.35rem; cursor:pointer;">
                            <input type="checkbox" name="agent_info_image_remove" value="1">
                            Remove current image
                        </label>
                    </div>
                @endif
                <small style="color:#64748b;">Upload agent info image (JPEG, PNG, WebP, max 5MB). Leave empty to keep current.</small>
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

        <h3 style="margin-bottom:1.5rem;">Hero Section</h3>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2rem;">
            <label class="workspace-field">
                <span>Hero Heading</span>
                <input type="text" name="content[hero_heading]" value="{{ old('content.hero_heading', $c['hero_heading'] ?? '') }}">
            </label>
            <label class="workspace-field">
                <span>Hero Subheading</span>
                <input type="text" name="content[hero_subheading]" value="{{ old('content.hero_subheading', $c['hero_subheading'] ?? '') }}">
            </label>
        </div>

        <hr style="margin:2rem 0; border:none; border-top:1px solid #eee;">

        <h3 style="margin-bottom:1.5rem;">Agent Info</h3>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2rem;">
            <label class="workspace-field">
                <span>Agent Name</span>
                <input type="text" name="content[agent_name]" value="{{ old('content.agent_name', $c['agent_name'] ?? '') }}">
            </label>
            <label class="workspace-field">
                <span>Agent Title</span>
                <input type="text" name="content[agent_title]" value="{{ old('content.agent_title', $c['agent_title'] ?? '') }}">
            </label>
            <div class="workspace-field workspace-field--full quill-wrap">
                <label>Agent Bio</label>
                <div id="quill-agent-bio">{!! old('content.agent_bio', $c['agent_bio'] ?? '') !!}</div>
                <textarea name="content[agent_bio]" id="quill-agent-bio-content" style="display:none;">{{ old('content.agent_bio', $c['agent_bio'] ?? '') }}</textarea>
            </div>
        </div>

        <hr style="margin:2rem 0; border:none; border-top:1px solid #eee;">

        <h3 style="margin-bottom:1.5rem;">Body Content</h3>
        <p style="font-size:.85rem; color:#666; margin-bottom:.5rem;">Main page content. Paste any formatted blocks here — headings, paragraphs, lists, etc.</p>
        <div class="workspace-field workspace-field--full quill-wrap">
            <div id="quill-body-content">{!! old('content.body_content', $c['body_content'] ?? '') !!}</div>
            <textarea name="content[body_content]" id="quill-body-content-field" style="display:none;">{{ old('content.body_content', $c['body_content'] ?? '') }}</textarea>
        </div>

        <hr style="margin:2rem 0; border:none; border-top:1px solid #eee;">

        <h3 style="margin-bottom:1rem;">CTA &amp; Form Settings</h3>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
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
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
let faqIndex = {{ count($c['faqs'] ?? []) }};

function initQuill(id, textareaId) {
    const editor = document.getElementById(id);
    if (!editor) return null;
    const quill = new Quill('#' + id, {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [2, 3, 4, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['blockquote', 'link'],
                ['clean']
            ]
        }
    });
    quill.format('bold', false);
    quill.format('header', false);

    quill.on('text-change', function() {
        document.getElementById(textareaId).value = quill.root.innerHTML;
    });
    return quill;
}

const quillAgentBio = initQuill('quill-agent-bio', 'quill-agent-bio-content');
const quillBodyContent = initQuill('quill-body-content', 'quill-body-content-field');

document.getElementById('seo-form').addEventListener('submit', function() {
    if (quillAgentBio) {
        document.getElementById('quill-agent-bio-content').value = quillAgentBio.root.innerHTML;
    }
    if (quillBodyContent) {
        document.getElementById('quill-body-content-field').value = quillBodyContent.root.innerHTML;
    }
});

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
