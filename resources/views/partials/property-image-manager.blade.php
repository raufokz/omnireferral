@php
    $existingImages = collect($existingImages ?? [])->filter(fn ($path) => is_string($path) && trim($path) !== '')->values();
    $featuredPath = (string) ($featuredImage ?? '');

    $featuredTokenFromServer = $featuredPath !== '' ? 'existing::' . $featuredPath : '';
    $featuredToken = old('featured_image', $featuredTokenFromServer);

    $resolveImageUrl = static function (string $path): string {
        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/storage/'])) {
            return $path;
        }

        if (\Illuminate\Support\Str::startsWith($path, 'storage/')) {
            return '/' . $path;
        }

        if (\Illuminate\Support\Str::startsWith($path, 'images/')) {
            return asset($path);
        }

        return \Illuminate\Support\Facades\Storage::url($path);
    };
@endphp

<div class="workspace-field workspace-field--full property-image-manager" data-property-image-manager data-max-file-mb="6">
    <span>Property Images</span>

    <div class="pim-dropzone" data-pim-dropzone tabindex="0" role="button" aria-label="Upload property images">
        <input type="file" name="images[]" accept="image/*" multiple data-pim-input class="pim-file-input">
        <div class="pim-dropzone__copy">
            <strong>Drop photos here or browse your device</strong>
            <span>Bulk upload as many images as you need. Each file can be up to 6MB.</span>
        </div>
    </div>

    <input type="hidden" name="featured_image" value="{{ $featuredToken }}" data-pim-featured-input>
    <div data-pim-order-inputs></div>
    <div data-pim-new-token-inputs></div>

    <div class="pim-toolbar">
        <div class="pim-toolbar__group">
            <span class="pim-toolbar__hint">Drag cards to reorder the gallery. The featured image powers the public card thumbnail and details hero.</span>
            <span class="pim-toolbar__meta">Hover or tap a photo to feature or remove it.</span>
        </div>
        <div class="pim-toolbar__group pim-toolbar__group--summary">
            <span class="pim-toolbar__count" data-pim-count>0 images selected</span>
            <span class="pim-toolbar__size" data-pim-size>0 MB total</span>
        </div>
    </div>

    <div class="pim-progress" data-pim-progress hidden>
        <span data-pim-progress-bar></span>
    </div>

    <div class="pim-grid" data-pim-grid>
        @foreach($existingImages as $path)
            @php
                $token = 'existing::' . $path;
                $isFeatured = $featuredToken === $token;
            @endphp
            <article class="pim-card {{ $isFeatured ? 'is-featured' : '' }}" data-pim-card data-token="{{ $token }}" data-kind="existing" draggable="true">
                <input type="hidden" name="existing_images[]" value="{{ $path }}" data-pim-existing-input>
                <img src="{{ $resolveImageUrl($path) }}" alt="Property image" loading="lazy" decoding="async">
                <span class="pim-badge" data-pim-badge>{{ $isFeatured ? 'Featured' : '' }}</span>
                <span class="pim-order-chip" data-pim-order-label></span>
                <div class="pim-actions">
                    <button type="button" class="pim-drag" data-pim-drag-handle aria-label="Drag to reorder">::</button>
                    <button type="button" class="button button--ghost-blue pim-action" data-pim-set-featured>Set as featured</button>
                    <button type="button" class="pim-remove" data-pim-remove aria-label="Remove image">x</button>
                </div>
            </article>
        @endforeach
    </div>

    <div class="pim-empty" data-pim-empty>
        No property images yet. Add a few strong exterior and interior shots to make the listing feel complete.
    </div>

    <small class="workspace-field__help">Files are previewed instantly before save. Removing a card updates the gallery right away.</small>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const managers = document.querySelectorAll('[data-property-image-manager]');

                managers.forEach((manager) => {
                    const input = manager.querySelector('[data-pim-input]');
                    const dropzone = manager.querySelector('[data-pim-dropzone]');
                    const grid = manager.querySelector('[data-pim-grid]');
                    const featuredInput = manager.querySelector('[data-pim-featured-input]');
                    const countLabel = manager.querySelector('[data-pim-count]');
                    const sizeLabel = manager.querySelector('[data-pim-size]');
                    const emptyState = manager.querySelector('[data-pim-empty]');
                    const orderInputs = manager.querySelector('[data-pim-order-inputs]');
                    const newTokenInputs = manager.querySelector('[data-pim-new-token-inputs]');
                    const progressWrap = manager.querySelector('[data-pim-progress]');
                    const progressBar = manager.querySelector('[data-pim-progress-bar]');
                    const maxFileMb = Number(manager.dataset.maxFileMb || '6');
                    const maxBytes = maxFileMb * 1024 * 1024;

                    if (!input || !dropzone || !grid || !featuredInput || !orderInputs || !newTokenInputs) {
                        return;
                    }

                    let newFiles = [];
                    let newFileIndex = 0;
                    let draggedCard = null;

                    const totalMegabytes = () => {
                        const totalBytes = newFiles.reduce((sum, item) => sum + item.file.size, 0);
                        return `${(totalBytes / (1024 * 1024)).toFixed(totalBytes > 0 ? 1 : 0)} MB total`;
                    };

                    const showProgress = (value) => {
                        if (!progressWrap || !progressBar) {
                            return;
                        }

                        progressWrap.hidden = false;
                        progressBar.style.width = `${Math.max(8, Math.min(100, value))}%`;

                        if (value >= 100) {
                            window.setTimeout(() => {
                                progressWrap.hidden = true;
                                progressBar.style.width = '0%';
                            }, 220);
                        }
                    };

                    const updateOrderLabels = () => {
                        Array.from(grid.querySelectorAll('[data-pim-card]')).forEach((card, index) => {
                            const label = card.querySelector('[data-pim-order-label]');
                            if (label) {
                                label.textContent = `#${index + 1}`;
                            }
                        });
                    };

                    const syncOrderInputs = () => {
                        orderInputs.replaceChildren();

                        Array.from(grid.querySelectorAll('[data-pim-card]')).forEach((card) => {
                            const hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.name = 'gallery_order[]';
                            hidden.value = card.dataset.token || '';
                            orderInputs.appendChild(hidden);
                        });

                        updateOrderLabels();
                    };

                    const syncNewTokenInputs = () => {
                        newTokenInputs.replaceChildren();

                        newFiles.forEach((item) => {
                            const hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.name = 'new_upload_tokens[]';
                            hidden.value = item.token;
                            newTokenInputs.appendChild(hidden);
                        });
                    };

                    const updateCount = () => {
                        const total = grid.querySelectorAll('[data-pim-card]').length;
                        if (countLabel) {
                            countLabel.textContent = `${total} image${total === 1 ? '' : 's'} selected`;
                        }
                        if (sizeLabel) {
                            sizeLabel.textContent = totalMegabytes();
                        }
                        if (emptyState) {
                            emptyState.hidden = total > 0;
                        }
                    };

                    const applyFeaturedState = () => {
                        const cards = Array.from(grid.querySelectorAll('[data-pim-card]'));
                        const current = featuredInput.value;

                        cards.forEach((card) => {
                            const token = card.dataset.token;
                            const isFeatured = token && token === current;
                            card.classList.toggle('is-featured', !!isFeatured);
                            const badge = card.querySelector('[data-pim-badge]');
                            if (badge) {
                                badge.textContent = isFeatured ? 'Featured' : '';
                            }
                        });

                        if (!cards.length) {
                            featuredInput.value = '';
                            return;
                        }

                        const hasMatch = cards.some((card) => card.dataset.token === featuredInput.value);
                        if (!hasMatch) {
                            featuredInput.value = cards[0].dataset.token || '';
                            applyFeaturedState();
                        }
                    };

                    const rebuildInputFiles = () => {
                        const data = new DataTransfer();
                        newFiles.forEach((item) => data.items.add(item.file));
                        input.files = data.files;
                        syncNewTokenInputs();
                    };

                    const syncNewFilesFromDom = () => {
                        const orderedTokens = Array.from(grid.querySelectorAll('[data-pim-card][data-kind="new"]'))
                            .map((card) => card.dataset.token)
                            .filter(Boolean);

                        newFiles = orderedTokens
                            .map((token) => newFiles.find((item) => item.token === token))
                            .filter(Boolean);

                        rebuildInputFiles();
                    };

                    const removeCardWithAnimation = (card, callback) => {
                        card.classList.add('is-removing');
                        window.setTimeout(() => {
                            callback();
                            syncOrderInputs();
                            applyFeaturedState();
                            updateCount();
                        }, 180);
                    };

                    const bindCard = (card) => {
                        const setFeatured = card.querySelector('[data-pim-set-featured]');
                        const remove = card.querySelector('[data-pim-remove]');

                        card.draggable = true;

                        setFeatured?.addEventListener('click', () => {
                            featuredInput.value = card.dataset.token || '';
                            applyFeaturedState();
                        });

                        remove?.addEventListener('click', () => {
                            const kind = card.dataset.kind;

                            if (kind === 'new') {
                                const token = card.dataset.token;
                                removeCardWithAnimation(card, () => {
                                    card.remove();
                                    newFiles = newFiles.filter((item) => item.token !== token);
                                    rebuildInputFiles();
                                });
                                return;
                            }

                            const existingInput = card.querySelector('[data-pim-existing-input]');
                            const path = existingInput?.value;

                            removeCardWithAnimation(card, () => {
                                if (path) {
                                    const hidden = document.createElement('input');
                                    hidden.type = 'hidden';
                                    hidden.name = 'remove_images[]';
                                    hidden.value = path;
                                    manager.appendChild(hidden);
                                }

                                card.remove();
                            });
                        });

                        card.addEventListener('dragstart', () => {
                            draggedCard = card;
                            card.classList.add('is-dragging');
                        });

                        card.addEventListener('dragend', () => {
                            card.classList.remove('is-dragging');
                            draggedCard = null;
                            syncNewFilesFromDom();
                            syncOrderInputs();
                            applyFeaturedState();
                        });
                    };

                    const createNewCard = (item) => {
                        const url = URL.createObjectURL(item.file);
                        const card = document.createElement('article');
                        card.className = 'pim-card';
                        card.dataset.pimCard = '';
                        card.dataset.token = item.token;
                        card.dataset.kind = 'new';
                        card.draggable = true;

                        card.innerHTML = `
                            <img src="${url}" alt="New property image preview" loading="lazy" decoding="async">
                            <span class="pim-badge" data-pim-badge></span>
                            <span class="pim-order-chip" data-pim-order-label></span>
                            <div class="pim-actions">
                                <button type="button" class="pim-drag" data-pim-drag-handle aria-label="Drag to reorder">::</button>
                                <button type="button" class="button button--ghost-blue pim-action" data-pim-set-featured>Set as featured</button>
                                <button type="button" class="pim-remove" data-pim-remove aria-label="Remove image">x</button>
                            </div>
                        `;

                        const image = card.querySelector('img');
                        image?.addEventListener('load', () => URL.revokeObjectURL(url), { once: true });

                        bindCard(card);
                        return card;
                    };

                    const renderNewCards = () => {
                        const existingCards = new Map(
                            Array.from(grid.querySelectorAll('[data-pim-card][data-kind="existing"]'))
                                .map((card) => [card.dataset.token, card])
                        );

                        const currentOrder = Array.from(grid.querySelectorAll('[data-pim-card]'))
                            .map((card) => card.dataset.token)
                            .filter(Boolean);

                        const desiredOrder = currentOrder.filter((token) => (
                            existingCards.has(token) || newFiles.some((item) => item.token === token)
                        ));

                        newFiles.forEach((item) => {
                            if (!desiredOrder.includes(item.token)) {
                                desiredOrder.push(item.token);
                            }
                        });

                        grid.replaceChildren();

                        desiredOrder.forEach((token) => {
                            if (existingCards.has(token)) {
                                grid.appendChild(existingCards.get(token));
                                return;
                            }

                            const item = newFiles.find((entry) => entry.token === token);
                            if (item) {
                                grid.appendChild(createNewCard(item));
                            }
                        });

                        syncOrderInputs();
                        applyFeaturedState();
                        updateCount();
                    };

                    const addFiles = async (files) => {
                        const accepted = [];

                        files.forEach((file) => {
                            if (!file.type.startsWith('image/')) {
                                return;
                            }

                            if (file.size > maxBytes) {
                                window.alert(`${file.name} is larger than ${maxFileMb}MB and was skipped.`);
                                return;
                            }

                            accepted.push({
                                token: `new::upload-${Date.now()}-${newFileIndex++}`,
                                file,
                            });
                        });

                        if (!accepted.length) {
                            return;
                        }

                        showProgress(12);

                        accepted.forEach((item, index) => {
                            newFiles.push(item);
                            showProgress(((index + 1) / accepted.length) * 100);
                        });

                        rebuildInputFiles();
                        renderNewCards();
                    };

                    input.addEventListener('change', (event) => {
                        const files = Array.from(event.target.files || []);
                        addFiles(files);
                    });

                    dropzone.addEventListener('click', () => input.click());
                    dropzone.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            input.click();
                        }
                    });

                    ['dragenter', 'dragover'].forEach((eventName) => {
                        dropzone.addEventListener(eventName, (event) => {
                            event.preventDefault();
                            dropzone.classList.add('is-dragging');
                        });
                    });

                    ['dragleave', 'drop'].forEach((eventName) => {
                        dropzone.addEventListener(eventName, (event) => {
                            event.preventDefault();
                            dropzone.classList.remove('is-dragging');
                        });
                    });

                    dropzone.addEventListener('drop', (event) => {
                        const dropped = Array.from(event.dataTransfer?.files || []);
                        addFiles(dropped);
                    });

                    grid.addEventListener('dragover', (event) => {
                        if (!draggedCard) {
                            return;
                        }

                        event.preventDefault();
                        const target = event.target.closest('[data-pim-card]');
                        if (!target || target === draggedCard) {
                            return;
                        }

                        const rect = target.getBoundingClientRect();
                        const insertAfter = event.clientY > rect.top + (rect.height / 2);
                        grid.insertBefore(draggedCard, insertAfter ? target.nextSibling : target);
                    });

                    grid.addEventListener('drop', (event) => {
                        if (draggedCard) {
                            event.preventDefault();
                        }
                    });

                    grid.querySelectorAll('[data-pim-card]').forEach(bindCard);
                    syncOrderInputs();
                    syncNewTokenInputs();
                    applyFeaturedState();
                    updateCount();
                });
            });
        </script>
    @endpush
@endonce
