@php
    $sections = old('content.sections', $page->content['sections'] ?? [['heading' => '', 'body' => '']]);
    $faqs = old('content.faqs', $page->content['faqs'] ?? [['question' => '', 'answer' => '']]);
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
</style>
@endpush

<div class="workspace-card" style="display:grid; gap:1rem;">
    <label>
        <span>Page Title</span>
        <input type="text" name="title" value="{{ old('title', $page->title) }}" required>
    </label>

    <label>
        <span>Slug</span>
        <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" placeholder="pay-at-closing-real-estate-leads">
    </label>

    <label>
        <span>SEO Title</span>
        <input type="text" name="seo_title" value="{{ old('seo_title', $page->seo_title) }}">
    </label>

    <label>
        <span>Meta Description</span>
        <textarea name="meta_description" rows="3">{{ old('meta_description', $page->meta_description) }}</textarea>
    </label>

    <label>
        <span>Canonical URL</span>
        <input type="url" name="canonical_url" value="{{ old('canonical_url', $page->canonical_url) }}">
    </label>

    <label>
        <span>Primary Keyword</span>
        <input type="text" name="primary_keyword" value="{{ old('primary_keyword', $page->primary_keyword) }}">
    </label>

    <label>
        <span>Secondary Keywords, one per line</span>
        <textarea name="secondary_keywords" rows="4">{{ old('secondary_keywords', $page->getSecondaryKeywordsText()) }}</textarea>
    </label>

    <label>
        <span>Hero Title</span>
        <input type="text" name="hero_title" value="{{ old('hero_title', $page->hero_title) }}">
    </label>

    <div class="service-seo-editor">
        <span>Hero Body</span>
        <div data-quill-editor>{!! old('hero_body', $page->hero_body) !!}</div>
        <textarea name="hero_body" data-quill-field style="display:none;">{{ old('hero_body', $page->hero_body) }}</textarea>
    </div>

    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:1rem;">
        <label>
            <span>CTA Label</span>
            <input type="text" name="cta_label" value="{{ old('cta_label', $page->cta_label) }}">
        </label>
        <label>
            <span>CTA URL</span>
            <input type="text" name="cta_url" value="{{ old('cta_url', $page->cta_url) }}" placeholder="/contact">
        </label>
    </div>

    <label style="display:flex; align-items:center; gap:.5rem;">
        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published))>
        <span>Published</span>
    </label>
</div>

<section class="workspace-card" style="margin-top:1rem;">
    <div style="display:flex; justify-content:space-between; gap:1rem; align-items:center; margin-bottom:1rem;">
        <h2 style="margin:0;">Content Sections</h2>
        <button type="button" class="button button--ghost-blue" data-add-section>Add Section</button>
    </div>
    <div data-sections style="display:grid; gap:1rem;">
        @foreach($sections as $index => $section)
            <div class="workspace-card" data-repeatable-item>
                <label>
                    <span>Heading</span>
                    <input type="text" name="content[sections][{{ $index }}][heading]" value="{{ $section['heading'] ?? '' }}">
                </label>
                <div class="service-seo-editor">
                    <span>Body</span>
                    <div data-quill-editor>{!! $section['body'] ?? '' !!}</div>
                    <textarea name="content[sections][{{ $index }}][body]" data-quill-field style="display:none;">{{ $section['body'] ?? '' }}</textarea>
                </div>
                <button type="button" class="button button--ghost-blue" style="color:#dc2626;" data-remove-item>Remove</button>
            </div>
        @endforeach
    </div>
</section>

<section class="workspace-card" style="margin-top:1rem;">
    <div style="display:flex; justify-content:space-between; gap:1rem; align-items:center; margin-bottom:1rem;">
        <h2 style="margin:0;">FAQs</h2>
        <button type="button" class="button button--ghost-blue" data-add-faq>Add FAQ</button>
    </div>
    <div data-faqs style="display:grid; gap:1rem;">
        @foreach($faqs as $index => $faq)
            <div class="workspace-card" data-repeatable-item>
                <label>
                    <span>Question</span>
                    <input type="text" name="content[faqs][{{ $index }}][question]" value="{{ $faq['question'] ?? '' }}">
                </label>
                <div class="service-seo-editor service-seo-editor--compact">
                    <span>Answer</span>
                    <div data-quill-editor>{!! $faq['answer'] ?? '' !!}</div>
                    <textarea name="content[faqs][{{ $index }}][answer]" data-quill-field style="display:none;">{{ $faq['answer'] ?? '' }}</textarea>
                </div>
                <button type="button" class="button button--ghost-blue" style="color:#dc2626;" data-remove-item>Remove</button>
            </div>
        @endforeach
    </div>
</section>

<div class="workspace-actions" style="margin-top:1rem;">
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
            item.className = 'workspace-card';
            item.dataset.repeatableItem = '';

            if (type === 'section') {
                item.innerHTML = `
                    <label><span>Heading</span><input type="text" name="content[sections][${index}][heading]"></label>
                    <div class="service-seo-editor">
                        <span>Body</span>
                        <div data-quill-editor></div>
                        <textarea name="content[sections][${index}][body]" data-quill-field style="display:none;"></textarea>
                    </div>
                    <button type="button" class="button button--ghost-blue" style="color:#dc2626;" data-remove-item>Remove</button>
                `;
            } else {
                item.innerHTML = `
                    <label><span>Question</span><input type="text" name="content[faqs][${index}][question]"></label>
                    <div class="service-seo-editor service-seo-editor--compact">
                        <span>Answer</span>
                        <div data-quill-editor></div>
                        <textarea name="content[faqs][${index}][answer]" data-quill-field style="display:none;"></textarea>
                    </div>
                    <button type="button" class="button button--ghost-blue" style="color:#dc2626;" data-remove-item>Remove</button>
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
