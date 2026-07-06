@php
    $sections = old('content.sections', $page->content['sections'] ?? [['heading' => '', 'body' => '', 'image' => '']]);
    $faqs = old('content.faqs', $page->content['faqs'] ?? [['question' => '', 'answer' => '']]);
    $heroImage = old('content.hero_image', $page->content['hero_image'] ?? '');
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<style>
    .service-seo-editor {
        display: grid;
        gap: 0.4rem;
    }

    .service-seo-editor > span {
        color: #334155;
        font-size: 0.85rem;
        font-weight: 700;
    }

    .service-seo-editor .ql-toolbar {
        border-color: rgba(11, 54, 104, 0.14);
        border-radius: 8px 8px 0 0;
        background: #f8fafc;
    }

    .service-seo-editor .ql-container {
        min-height: 180px;
        border-color: rgba(11, 54, 104, 0.14);
        border-radius: 0 0 8px 8px;
        background: #fff;
        font-size: 0.95rem;
    }

    .service-seo-editor--compact .ql-container {
        min-height: 130px;
    }

    .seo-section-card {
        border: 1px solid var(--workspace-border, rgba(11, 54, 104, 0.12));
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 4px 16px rgba(15, 33, 61, 0.06);
        padding: 1.2rem;
        display: grid;
        gap: 0.85rem;
        position: relative;
    }

    .seo-section-card .remove-btn {
        position: absolute;
        top: 0.6rem;
        right: 0.6rem;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.3rem 0.6rem;
        border: 1px solid #fecaca;
        border-radius: 6px;
        background: #fef2f2;
        color: #dc2626;
        font-size: 0.75rem;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.15s;
    }

    .seo-section-card .remove-btn:hover {
        background: #fee2e2;
    }

    .section-header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--workspace-border, rgba(11, 54, 104, 0.12));
    }

    .section-header-bar h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: var(--service-navy, #06152b);
    }

    .seo-card-grid {
        display: grid;
        gap: 1rem;
    }

    .seo-field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.85rem;
    }

    @media (max-width: 640px) {
        .seo-field-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

<div class="workspace-card seo-card-grid">
    <div class="section-header-bar">
        <h3>Page Details</h3>
    </div>

    <div class="workspace-form-grid">
        <label class="workspace-field workspace-field--full">
            <span>Page Title</span>
            <input type="text" name="title" value="{{ old('title', $page->title) }}" required>
        </label>

        <label class="workspace-field workspace-field--full">
            <span>Slug</span>
            <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" placeholder="pay-at-closing-real-estate-leads">
        </label>

        <label class="workspace-field workspace-field--full">
            <span>SEO Title</span>
            <input type="text" name="seo_title" value="{{ old('seo_title', $page->seo_title) }}">
        </label>

        <label class="workspace-field workspace-field--full">
            <span>Meta Description</span>
            <textarea name="meta_description" rows="3">{{ old('meta_description', $page->meta_description) }}</textarea>
        </label>

        <label class="workspace-field workspace-field--full">
            <span>Canonical URL</span>
            <input type="text" name="canonical_url" value="{{ old('canonical_url', $page->canonical_url) }}">
        </label>

        <label class="workspace-field">
            <span>Primary Keyword</span>
            <input type="text" name="primary_keyword" value="{{ old('primary_keyword', $page->primary_keyword) }}">
        </label>

        <label class="workspace-field">
            <span>Secondary Keywords</span>
            <textarea name="secondary_keywords" rows="4">{{ old('secondary_keywords', $page->getSecondaryKeywordsText()) }}</textarea>
        </label>
    </div>
</div>

<div class="workspace-card seo-card-grid" style="margin-top:1rem;">
    <div class="section-header-bar">
        <h3>Hero Section</h3>
    </div>

    <div class="workspace-form-grid">
        <label class="workspace-field workspace-field--full">
            <span>Hero Title</span>
            <input type="text" name="hero_title" value="{{ old('hero_title', $page->hero_title) }}">
        </label>

        <label class="workspace-field workspace-field--full">
            <span>Hero Body</span>
        </label>
    </div>

    <div class="service-seo-editor">
        <span>Hero Body Content</span>
        <div data-quill-editor>{!! old('hero_body', $page->hero_body) !!}</div>
        <textarea name="hero_body" data-quill-field style="display:none;">{{ old('hero_body', $page->hero_body) }}</textarea>
    </div>

    <div class="seo-field-row">
        <label class="workspace-field" style="margin:0;">
            <span>CTA Label</span>
            <input type="text" name="cta_label" value="{{ old('cta_label', $page->cta_label) }}">
        </label>

        <label class="workspace-field" style="margin:0;">
            <span>CTA URL</span>
            <input type="text" name="cta_url" value="{{ old('cta_url', $page->cta_url) }}" placeholder="/contact">
        </label>
    </div>

    <label class="workspace-field workspace-field--full" style="margin:0;">
        <span>Hero Image URL</span>
        <input type="text" name="content[hero_image]" value="{{ $heroImage }}" placeholder="/images/services/hero.jpg">
    </label>

    <label style="display:flex; align-items:center; gap:.5rem; padding-top:0.5rem; border-top:1px solid var(--workspace-border, rgba(11, 54, 104, 0.12));">
        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published))>
        <span style="font-weight:700; font-size:0.88rem; color:#334155;">Published</span>
    </label>
</div>

<div class="workspace-card seo-card-grid" style="margin-top:1rem;">
    <div class="section-header-bar">
        <h3>Content Sections</h3>
        <button type="button" class="button button--ghost-blue" data-add-section>+ Add Section</button>
    </div>

    <div data-sections class="seo-card-grid">
        @foreach($sections as $index => $section)
            <div class="seo-section-card" data-repeatable-item>
                <button type="button" class="remove-btn" data-remove-item>&times; Remove</button>
                <label class="workspace-field workspace-field--full" style="margin:0;">
                    <span>Heading</span>
                    <input type="text" name="content[sections][{{ $index }}][heading]" value="{{ $section['heading'] ?? '' }}">
                </label>
                <div class="service-seo-editor" style="margin:0;">
                    <span>Body</span>
                    <div data-quill-editor>{!! $section['body'] ?? '' !!}</div>
                    <textarea name="content[sections][{{ $index }}][body]" data-quill-field style="display:none;">{{ $section['body'] ?? '' }}</textarea>
                </div>
                <label class="workspace-field workspace-field--full" style="margin:0;">
                    <span>Image URL (optional)</span>
                    <input type="text" name="content[sections][{{ $index }}][image]" value="{{ $section['image'] ?? '' }}" placeholder="/images/services/feature.jpg">
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="workspace-card seo-card-grid" style="margin-top:1rem;">
    <div class="section-header-bar">
        <h3>FAQs</h3>
        <button type="button" class="button button--ghost-blue" data-add-faq>+ Add FAQ</button>
    </div>

    <div data-faqs class="seo-card-grid">
        @foreach($faqs as $index => $faq)
            <div class="seo-section-card" data-repeatable-item>
                <button type="button" class="remove-btn" data-remove-item>&times; Remove</button>
                <label class="workspace-field workspace-field--full" style="margin:0;">
                    <span>Question</span>
                    <input type="text" name="content[faqs][{{ $index }}][question]" value="{{ $faq['question'] ?? '' }}">
                </label>
                <div class="service-seo-editor service-seo-editor--compact" style="margin:0;">
                    <span>Answer</span>
                    <div data-quill-editor>{!! $faq['answer'] ?? '' !!}</div>
                    <textarea name="content[faqs][{{ $index }}][answer]" data-quill-field style="display:none;">{{ $faq['answer'] ?? '' }}</textarea>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="workspace-actions" style="margin-top:1.5rem;">
    <button type="submit" class="button">{{ $submitLabel }}</button>
    <a href="{{ route('admin.service-seo-pages.index') }}" class="button button--ghost-blue">Cancel</a>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const quillInstances = [];
        const toolbarOptions = [
            [{ header: [2, 3, false] }],
            ['bold', 'italic', 'underline'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['link'],
            ['clean']
        ];

        const syncQuill = (quill, field) => {
            field.value = quill.root.innerHTML;
        };

        const initQuill = (scope = document) => {
            if (typeof Quill === 'undefined') return;

            scope.querySelectorAll('[data-quill-editor]:not([data-quill-ready])').forEach((editor) => {
                const field = editor.parentElement.querySelector('[data-quill-field]');
                if (!field) return;

                editor.dataset.quillReady = '1';
                const quill = new Quill(editor, {
                    theme: 'snow',
                    modules: { toolbar: toolbarOptions },
                    placeholder: 'Write polished SEO content here...'
                });

                syncQuill(quill, field);
                quill.on('text-change', () => syncQuill(quill, field));
                quillInstances.push({ quill, field });
            });
        };

        const addItem = (wrapSelector, type) => {
            const wrap = document.querySelector(wrapSelector);
            if (!wrap) return;
            const index = wrap.querySelectorAll('[data-repeatable-item]').length;
            const item = document.createElement('div');
            item.className = 'seo-section-card';
            item.dataset.repeatableItem = '';

            if (type === 'section') {
                item.innerHTML = `
                    <button type="button" class="remove-btn" data-remove-item>&times; Remove</button>
                    <label class="workspace-field workspace-field--full" style="margin:0;">
                        <span>Heading</span>
                        <input type="text" name="content[sections][${index}][heading]">
                    </label>
                    <div class="service-seo-editor" style="margin:0;">
                        <span>Body</span>
                        <div data-quill-editor></div>
                        <textarea name="content[sections][${index}][body]" data-quill-field style="display:none;"></textarea>
                    </div>
                    <label class="workspace-field workspace-field--full" style="margin:0;">
                        <span>Image URL (optional)</span>
                        <input type="text" name="content[sections][${index}][image]" placeholder="/images/services/feature.jpg">
                    </label>
                `;
            } else {
                item.innerHTML = `
                    <button type="button" class="remove-btn" data-remove-item>&times; Remove</button>
                    <label class="workspace-field workspace-field--full" style="margin:0;">
                        <span>Question</span>
                        <input type="text" name="content[faqs][${index}][question]">
                    </label>
                    <div class="service-seo-editor service-seo-editor--compact" style="margin:0;">
                        <span>Answer</span>
                        <div data-quill-editor></div>
                        <textarea name="content[faqs][${index}][answer]" data-quill-field style="display:none;"></textarea>
                    </div>
                `;
            }

            wrap.appendChild(item);
            initQuill(item);
        };

        initQuill();

        document.querySelector('form')?.addEventListener('submit', () => {
            quillInstances.forEach(({ quill, field }) => syncQuill(quill, field));
        });

        document.querySelector('[data-add-section]')?.addEventListener('click', () => addItem('[data-sections]', 'section'));
        document.querySelector('[data-add-faq]')?.addEventListener('click', () => addItem('[data-faqs]', 'faq'));
        document.addEventListener('click', (event) => {
            if (event.target.matches('[data-remove-item]')) {
                event.target.closest('[data-repeatable-item]')?.remove();
            }
        });
    });
</script>
@endpush