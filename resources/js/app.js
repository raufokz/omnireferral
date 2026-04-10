import './bootstrap';
import Alpine from 'alpinejs';

document.documentElement.classList.add('js');

window.Alpine = Alpine;
Alpine.start();

// --- Configuration & Helpers ---
const HEADER_OFFSET = 24;
const SCROLL_THRESHOLD = 24;
const MOBILE_NAV_BREAKPOINT = 1100;

const showToast = (message, type = 'info', duration = 5000) => {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    toast.innerHTML = `
        <div class="toast__content">
            <span class="toast__message">${message}</span>
        </div>
        <button class="toast__close" aria-label="Close">×</button>
    `;

    container.appendChild(toast);
    setTimeout(() => toast.classList.add('is-visible'), 10);

    const remove = () => {
        toast.classList.remove('is-visible');
        setTimeout(() => toast.remove(), 300);
    };

    toast.querySelector('.toast__close').addEventListener('click', remove);
    if (duration > 0) setTimeout(remove, duration);
};
window.showToast = showToast;

// --- DOM elements ---
const siteHeader = document.getElementById('siteHeader') || document.querySelector('.site-header');
const menuToggle = document.getElementById('menuToggle');
const mainNav = document.getElementById('mainNav');
const navDropdowns = Array.from(document.querySelectorAll('[data-nav-dropdown]'));

// --- Header & Navigation Logic ---
// Scroll effect removed as per request.

if (menuToggle && mainNav) {
    const closeNavDropdowns = () => {
        navDropdowns.forEach((dropdown) => {
            dropdown.classList.remove('is-open');
            dropdown.querySelector('[data-nav-submenu-toggle]')?.setAttribute('aria-expanded', 'false');
        });
    };

    const toggleMenu = (open) => {
        const isOpen = typeof open === 'boolean' ? open : !mainNav.classList.contains('is-open');
        mainNav.classList.toggle('is-open', isOpen);
        menuToggle.classList.toggle('is-open', isOpen);
        menuToggle.setAttribute('aria-expanded', isOpen);
        document.body.classList.toggle('menu-open', isOpen);
        document.body.style.overflow = isOpen ? 'hidden' : '';

        if (!isOpen) {
            closeNavDropdowns();
        }
    };

    menuToggle.addEventListener('click', toggleMenu);
    mainNav.querySelectorAll('a').forEach(link => link.addEventListener('click', () => toggleMenu(false)));
    document.addEventListener('keydown', e => e.key === 'Escape' && toggleMenu(false));
    window.addEventListener('resize', () => {
        if (window.innerWidth > MOBILE_NAV_BREAKPOINT) {
            toggleMenu(false);
        }
    });
}

const initNavDropdowns = () => {
    if (!navDropdowns.length) return;

    const closeDropdowns = (except = null) => {
        navDropdowns.forEach((dropdown) => {
            if (dropdown === except) return;
            dropdown.classList.remove('is-open');
            dropdown.querySelector('[data-nav-submenu-toggle]')?.setAttribute('aria-expanded', 'false');
        });
    };

    navDropdowns.forEach((dropdown) => {
        const toggle = dropdown.querySelector('[data-nav-submenu-toggle]');
        if (!toggle) return;

        const setOpen = (open) => {
            dropdown.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        };

        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            const shouldOpen = !dropdown.classList.contains('is-open');
            closeDropdowns(dropdown);
            setOpen(shouldOpen);
        });

        dropdown.addEventListener('mouseenter', () => {
            if (window.innerWidth > MOBILE_NAV_BREAKPOINT) {
                closeDropdowns(dropdown);
                setOpen(true);
            }
        });

        dropdown.addEventListener('mouseleave', () => {
            if (window.innerWidth > MOBILE_NAV_BREAKPOINT) {
                setOpen(false);
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (!navDropdowns.some((dropdown) => dropdown.contains(event.target))) {
            closeDropdowns();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeDropdowns();
        }
    });
};

// --- Smooth Scrolling ---
const scrollToHash = (hash) => {
    const target = document.querySelector(hash);
    if (!target) return;

    const offset = (siteHeader?.offsetHeight || 0) + HEADER_OFFSET;
    const top = target.getBoundingClientRect().top + window.scrollY - offset;

    window.scrollTo({ top, behavior: 'smooth' });
};

document.querySelectorAll('a[href*="#"]').forEach(link => {
    link.addEventListener('click', (e) => {
        const url = new URL(link.href, window.location.href);
        if (url.pathname === window.location.pathname && url.hash) {
            e.preventDefault();
            scrollToHash(url.hash);
        }
    });
});

// --- Section Tracking ---
const navLinks = Array.from(document.querySelectorAll('.main-nav a[data-nav-section]'));
if (document.body.classList.contains('page-home') && navLinks.length) {
    const targets = navLinks.map(link => ({
        link,
        el: document.getElementById(link.dataset.navSection)
    })).filter(t => t.el);

    const observer = new IntersectionObserver((entries) => {
        const visible = entries.find(e => e.isIntersecting);
        if (!visible) return;

        targets.forEach(({ link, el }) => {
            const active = el === visible.target;
            link.classList.toggle('is-active', active);
            link.setAttribute('aria-current', active ? 'page' : 'false');
        });
    }, { threshold: 0.3, rootMargin: '-80px 0px -50% 0px' });

    targets.forEach(t => observer.observe(t.el));
}

// --- Reveal Animations ---
const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const el = entry.target;
            const delay = parseInt(el.dataset.revealDelay || 0, 10);
            setTimeout(() => el.classList.add('is-visible'), delay);
            revealObserver.unobserve(el);
        }
    });
}, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

const initAnimations = () => {
    document.querySelectorAll('[data-reveal], [data-animate], [data-stagger]').forEach(el => {
        if (el.hasAttribute('data-stagger')) {
            Array.from(el.children).forEach((child, i) => {
                child.dataset.reveal = child.dataset.reveal || '';
                child.dataset.revealDelay = i * 100;
                revealObserver.observe(child);
            });
        } else {
            revealObserver.observe(el);
        }
    });
};

// --- Tab Switchers ---
const initTabSwitchers = () => {
    document.querySelectorAll('[role="tablist"]').forEach((tablist) => {
        const triggers = Array.from(tablist.querySelectorAll('[data-tab-trigger]'));
        if (!triggers.length) return;

        const scope = tablist.parentElement || document;
        const panels = new Map(
            Array.from(scope.querySelectorAll('[data-tab-panel]')).map((panel) => [panel.dataset.tabPanel, panel])
        );

        const activateTab = (name, shouldFocus = false) => {
            triggers.forEach((trigger) => {
                const isActive = trigger.dataset.tabTrigger === name;
                trigger.classList.toggle('is-active', isActive);
                trigger.setAttribute('aria-selected', isActive ? 'true' : 'false');
                trigger.tabIndex = isActive ? 0 : -1;

                if (isActive && shouldFocus) {
                    trigger.focus();
                }
            });

            panels.forEach((panel, panelName) => {
                const isActive = panelName === name;
                panel.classList.toggle('is-active', isActive);
                panel.hidden = !isActive;
            });
        };

        triggers.forEach((trigger, index) => {
            trigger.addEventListener('click', () => activateTab(trigger.dataset.tabTrigger));

            trigger.addEventListener('keydown', (event) => {
                let nextIndex = null;

                if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
                    nextIndex = (index + 1) % triggers.length;
                } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
                    nextIndex = (index - 1 + triggers.length) % triggers.length;
                } else if (event.key === 'Home') {
                    nextIndex = 0;
                } else if (event.key === 'End') {
                    nextIndex = triggers.length - 1;
                }

                if (nextIndex === null) return;

                event.preventDefault();
                activateTab(triggers[nextIndex].dataset.tabTrigger, true);
            });
        });

        const activeTrigger = triggers.find((trigger) =>
            trigger.classList.contains('is-active') || trigger.getAttribute('aria-selected') === 'true'
        ) || triggers[0];

        activateTab(activeTrigger.dataset.tabTrigger);
    });
};

const initAgentDirectoryFilters = () => {
    const grid = document.getElementById('agentDirectoryGrid');
    const cityFilter = document.getElementById('agentCityFilter');
    const specialtyFilter = document.getElementById('agentSpecialtyFilter');
    const countEl = document.getElementById('agentDirectoryCount');
    const emptyState = document.getElementById('agentDirectoryEmpty');
    const pagination = document.querySelector('.pagination-wrap');
    const resetButtons = [
        document.getElementById('agentFilterReset'),
        document.getElementById('agentEmptyReset')
    ].filter(Boolean);

    if (!grid || !cityFilter || !specialtyFilter) return;

    const cards = Array.from(grid.querySelectorAll('[data-agent-card]'));

    const updateFilters = () => {
        const cityValue = cityFilter.value.trim().toLowerCase();
        const specialtyValue = specialtyFilter.value.trim().toLowerCase();

        const visibleCards = cards.filter((card) => {
            const matchesCity = !cityValue || card.dataset.city === cityValue;
            const matchesSpecialty = !specialtyValue || card.dataset.specialty === specialtyValue;
            const isVisible = matchesCity && matchesSpecialty;

            card.hidden = !isVisible;
            card.style.display = isVisible ? '' : 'none';

            return isVisible;
        });

        if (countEl) {
            const label = visibleCards.length === 1 ? 'agent' : 'agents';
            countEl.textContent = `Showing ${visibleCards.length} ${label} on this page`;
        }

        if (emptyState) {
            emptyState.hidden = visibleCards.length > 0;
        }

        if (pagination) {
            pagination.hidden = visibleCards.length === 0;
        }
    };

    [cityFilter, specialtyFilter].forEach((filter) => {
        filter.addEventListener('change', updateFilters);
    });

    resetButtons.forEach((button) => {
        button.addEventListener('click', () => {
            cityFilter.value = '';
            specialtyFilter.value = '';
            updateFilters();
        });
    });

    updateFilters();
};

const initEmbedLoaders = () => {
    const embeds = Array.from(document.querySelectorAll('[data-embed-loader]'));

    embeds.forEach((embed) => {
        const frame = embed.querySelector('[data-embed-loader-frame], iframe');
        const loader = embed.querySelector('[data-embed-loader-indicator]');
        const loaderCopy = loader?.querySelector('.embed-card__loader-copy');

        if (!frame) return;

        let isReady = false;
        let delayTimer = null;

        const setReady = (ready) => {
            embed.classList.toggle('is-loading', !ready);
            embed.classList.toggle('is-loaded', ready);
            embed.setAttribute('aria-busy', ready ? 'false' : 'true');

            if (loader) {
                loader.hidden = ready;
            }
        };

        const completeLoading = () => {
            if (isReady) return;

            isReady = true;
            if (delayTimer) {
                window.clearTimeout(delayTimer);
            }
            setReady(true);
        };

        if (!frame.getAttribute('src')) {
            completeLoading();
            return;
        }

        setReady(false);

        delayTimer = window.setTimeout(() => {
            if (!isReady && loaderCopy) {
                loaderCopy.textContent = 'Still connecting to the secure form. This can take a few extra seconds on slower connections.';
            }
        }, 6000);

        frame.addEventListener('load', completeLoading, { once: true });
        frame.addEventListener('error', () => {
            if (loaderCopy) {
                loaderCopy.textContent = 'The form is taking longer than expected. Please refresh the page or contact support if it does not appear.';
            }
        });
    });
};

const initCarousels = () => {
    document.querySelectorAll('[data-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('.testimonial-track');
        const cards = Array.from(track?.querySelectorAll('.testimonial-card') ?? []);
        const prevButton = carousel.querySelector('[data-carousel-prev]');
        const nextButton = carousel.querySelector('[data-carousel-next]');
        const status = carousel.querySelector('[data-carousel-status]');
        const progress = carousel.querySelector('[data-carousel-progress]');

        if (!track || cards.length === 0) return;

        let currentIndex = 0;
        let autoRotate = null;

        const visibleCount = () => {
            if (window.innerWidth <= 700) return 1;
            if (window.innerWidth <= 1100) return 2;
            return 3;
        };

        const maxIndex = () => Math.max(cards.length - visibleCount(), 0);

        const slideWidth = () => {
            const cardWidth = cards[0].getBoundingClientRect().width;
            const gap = parseFloat(window.getComputedStyle(track).gap || '0');
            return cardWidth + gap;
        };

        const render = () => {
            currentIndex = Math.min(currentIndex, maxIndex());
            track.style.transform = `translateX(-${currentIndex * slideWidth()}px)`;

            if (prevButton) {
                prevButton.disabled = currentIndex === 0;
            }

            if (nextButton) {
                nextButton.disabled = currentIndex >= maxIndex();
            }

            if (status) {
                const pages = Math.max(cards.length - visibleCount() + 1, 1);
                status.textContent = `${Math.min(currentIndex + 1, pages)} / ${pages}`;

                if (progress) {
                    const completion = pages <= 1 ? 1 : currentIndex / (pages - 1);
                    progress.style.width = `${Math.max(completion * 100, 12)}%`;
                }
            }
        };

        const startAutoRotate = () => {
            if (cards.length <= visibleCount()) return;

            window.clearInterval(autoRotate);
            autoRotate = window.setInterval(() => {
                currentIndex = currentIndex >= maxIndex() ? 0 : currentIndex + 1;
                render();
            }, 5200);
        };

        prevButton?.addEventListener('click', () => {
            currentIndex = Math.max(currentIndex - 1, 0);
            render();
            startAutoRotate();
        });

        nextButton?.addEventListener('click', () => {
            currentIndex = Math.min(currentIndex + 1, maxIndex());
            render();
            startAutoRotate();
        });

        carousel.addEventListener('mouseenter', () => window.clearInterval(autoRotate));
        carousel.addEventListener('mouseleave', startAutoRotate);
        window.addEventListener('resize', render, { passive: true });

        render();
        startAutoRotate();
    });
};

// --- Multi-Step Forms ---
document.querySelectorAll('[data-multi-step]').forEach(form => {
    const steps = Array.from(form.querySelectorAll('.form-step'));
    const progress = form.querySelector('.form-progress-bar > div');
    let current = 0;

    const showStep = (idx) => {
        current = Math.min(Math.max(idx, 0), steps.length - 1);
        steps.forEach((s, i) => {
            s.classList.toggle('is-active', i === current);
            s.hidden = i !== current;
        });
        if (progress) progress.style.width = `${((current + 1) / steps.length) * 100}%`;
    };

    form.querySelectorAll('[data-form-next]').forEach(btn => {
        btn.addEventListener('click', () => {
            const valid = Array.from(steps[current].querySelectorAll('input, select, textarea')).every(f => f.reportValidity());
            if (valid) showStep(current + 1);
        });
    });

    form.querySelectorAll('[data-form-prev]').forEach(btn => {
        btn.addEventListener('click', () => showStep(current - 1));
    });

    showStep(0);
});

// --- Dynamic Map Support ---
window.initHomeMap = () => {
    const mapEl = document.getElementById('homeMap') || document.getElementById('hero-map');
    if (!mapEl || typeof google === 'undefined') return;

    const map = new google.maps.Map(mapEl, {
        center: { lat: 39.8, lng: -98.5 },
        zoom: 4,
        disableDefaultUI: true,
        styles: [{ featureType: 'poi', stylers: [{ visibility: 'off' }] }]
    });

    const marker = new google.maps.Marker({ map, position: { lat: 39.8, lng: -98.5 } });
    const geocoder = new google.maps.Geocoder();

    window.updateMapZip = (zip) => {
        if (!zip || zip.length < 5) return;
        geocoder.geocode({ address: zip }, (results, status) => {
            if (status === 'OK' && results[0]) {
                const pos = results[0].geometry.location;
                map.setCenter(pos);
                map.setZoom(10);
                marker.setPosition(pos);
            }
        });
    };

    const zipInput = document.querySelector('input[name="zip_code"]');
    zipInput?.addEventListener('input', e => window.updateMapZip(e.target.value));
};

// --- Initialization ---
document.addEventListener('DOMContentLoaded', () => {
    initAnimations();
    initNavDropdowns();
    initTabSwitchers();
    initAgentDirectoryFilters();
    initEmbedLoaders();
    initCarousels();
    if (window.initHomeMap) window.initHomeMap();

    // Lazy load images
    document.querySelectorAll('img:not([loading])').forEach(img => img.setAttribute('loading', 'lazy'));

    // Global click handlers for UX
    document.addEventListener('click', e => {
        // Close modals/dropdowns if needed
    });
});

window.addEventListener('error', e => console.error('App Error:', e.error));
window.addEventListener('unhandledrejection', e => console.error('Promise Error:', e.reason));
