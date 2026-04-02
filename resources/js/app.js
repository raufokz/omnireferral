import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// --- Enhanced Header Scroll State ---
const header = document.getElementById('siteHeader');
if (header) {
    let lastScrollY = window.scrollY;
    let ticking = false;

    const updateHeader = () => {
        const scrollY = window.scrollY;
        const isScrolled = scrollY > 24;
        const isScrollingUp = scrollY < lastScrollY;

        header.classList.toggle('is-scrolled', isScrolled);

        // Hide/show header on scroll (mobile enhancement)
        if (window.innerWidth <= 768) {
            if (isScrolled && !isScrollingUp && scrollY > 100) {
                header.style.transform = 'translateY(-100%)';
            } else {
                header.style.transform = 'translateY(0)';
            }
        }

        lastScrollY = scrollY;
        ticking = false;
    };

    const onScroll = () => {
        if (!ticking) {
            requestAnimationFrame(updateHeader);
            ticking = true;
        }
    };

    updateHeader();
    window.addEventListener('scroll', onScroll, { passive: true });
}

// --- Enhanced Mobile Navigation ---
const menuToggle = document.getElementById('menuToggle');
const mainNav = document.getElementById('mainNav');
const navActions = document.getElementById('navActions');

if (menuToggle && mainNav) {
    const syncMenuState = (open) => {
        mainNav.classList.toggle('is-open', open);
        menuToggle.classList.toggle('is-open', open);
        menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');

        // Prevent body scroll when menu is open
        document.body.style.overflow = open ? 'hidden' : '';

        // Animate menu items with stagger
        if (open) {
            const menuItems = mainNav.querySelectorAll('a');
            menuItems.forEach((item, index) => {
                item.style.animationDelay = `${index * 50}ms`;
                item.style.animation = 'fadeInUp 0.3s ease forwards';
            });
        }
    };

    menuToggle.addEventListener('click', () => syncMenuState(!mainNav.classList.contains('is-open')));

    // Close menu on link click
    mainNav.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => syncMenuState(false));
    });

    // Close on escape key
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && mainNav.classList.contains('is-open')) {
            syncMenuState(false);
        }
    });

    // Close on outside click
    document.addEventListener('click', (event) => {
        if (!mainNav.contains(event.target) && !menuToggle.contains(event.target) && mainNav.classList.contains('is-open')) {
            syncMenuState(false);
        }
    });

    // Handle window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth > 1024 && mainNav.classList.contains('is-open')) {
            syncMenuState(false);
        }
    });
}

// --- Enhanced Smooth Scrolling ---
const siteHeader = document.getElementById('siteHeader');
const navSectionLinks = Array.from(document.querySelectorAll('.main-nav a[data-nav-section]'));

const updateHeaderState = () => {
    if (siteHeader) {
        siteHeader.classList.toggle('is-scrolled', window.scrollY > 12);
    }
};

const scrollToHashTarget = (hash) => {
    if (!hash || hash === '#') {
        return;
    }

    const target = document.querySelector(hash);
    if (!target) {
        return;
    }

    const offset = (siteHeader?.offsetHeight || 0) + 24;
    const top = target.getBoundingClientRect().top + window.scrollY - offset;

    window.scrollTo({
        top,
        behavior: 'smooth'
    });
};

// Enhanced link handling with loading states
document.querySelectorAll('a[href*="#"]').forEach((link) => {
    link.addEventListener('click', (event) => {
        const href = link.getAttribute('href') || '';
        if (!href.includes('#')) {
            return;
        }

        const url = new URL(href, window.location.href);
        const samePage = url.pathname === window.location.pathname;
        const hash = url.hash;

        if (samePage && hash) {
            event.preventDefault();
            scrollToHashTarget(hash);

            // Close mobile menu if open
            if (menuToggle && mainNav?.classList.contains('is-open')) {
                mainNav.classList.remove('is-open');
                menuToggle.classList.remove('is-open');
                menuToggle.setAttribute('aria-expanded', 'false');
            }
        }
    });
});

// --- Active Navigation Section Tracking ---
if (document.body.classList.contains('page-home') && navSectionLinks.length) {
    const sectionTargets = navSectionLinks
        .map((link) => ({
            link,
            section: document.getElementById(link.dataset.navSection || ''),
        }))
        .filter((item) => item.section);

    const activeNavObserver = new IntersectionObserver((entries) => {
        const visible = entries
            .filter((entry) => entry.isIntersecting)
            .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

        if (!visible) {
            return;
        }

        sectionTargets.forEach(({ link, section }) => {
            const active = section === visible.target;
            link.classList.toggle('is-active', active);
            link.setAttribute('aria-current', active ? 'page' : 'false');
        });
    }, {
        threshold: 0.35,
        rootMargin: '-120px 0px -45% 0px'
    });

    sectionTargets.forEach(({ section }) => activeNavObserver.observe(section));
}

updateHeaderState();
window.addEventListener('scroll', updateHeaderState, { passive: true });

// --- Enhanced Form Handling ---
    // Multi-zip tag input
    document.querySelectorAll('[data-zip-tags]').forEach((wrap) => {
        const hidden = wrap.querySelector('input[name="zip_code"]');
        const entry = wrap.querySelector('[data-zip-entry]');
        const addBtn = wrap.querySelector('[data-zip-add]');
        const list = wrap.querySelector('.zip-tag-list');
        let tags = [];

        const renderTags = () => {
            if (!list) return;
            list.innerHTML = '';
            tags.forEach((zip) => {
                const pill = document.createElement('span');
                pill.className = 'zip-tag-pill';
                pill.innerHTML = `${zip}<button type="button" aria-label="Remove ${zip}">×</button>`;
                pill.querySelector('button')?.addEventListener('click', () => {
                    tags = tags.filter((t) => t !== zip);
                    syncValue();
                });
                list.appendChild(pill);
            });
        };

        const syncValue = () => {
            if (hidden) {
                hidden.value = tags.join(',');
            }
            renderTags();
        };

        const addZip = (value) => {
            const zip = (value || '').trim();
            if (!zip) return;
            if (!tags.includes(zip)) {
                tags.push(zip);
                syncValue();
            }
            if (entry) entry.value = '';
        };

        entry?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                addZip(entry.value);
            }
        });

        addBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            addZip(entry?.value || '');
        });

        wrap.closest('form')?.addEventListener('submit', () => {
            addZip(entry?.value || '');
        });
    });

document.querySelectorAll('[data-multi-step]').forEach((form) => {
    const steps = Array.from(form.querySelectorAll('.form-step'));
    const progressBar = form.querySelector('.form-progress-bar > div');
    const stepCurrent = form.querySelector('.step-current');
    const stepPct = form.querySelector('.step-pct');
    let currentStep = Math.max(steps.findIndex((step) => step.classList.contains('is-active')), 0);

    const updateProgress = () => {
        if (!steps.length) {
            return;
        }

        const progress = ((currentStep + 1) / steps.length) * 100;

        if (progressBar) {
            progressBar.style.width = progress + '%';
            progressBar.style.transition = 'width 0.3s ease';
        }

        if (stepCurrent) {
            stepCurrent.textContent = String(currentStep + 1);
        }

        if (stepPct) {
            stepPct.textContent = Math.round(progress) + '%';
        }
    };

    const showStep = (index) => {
        currentStep = Math.min(Math.max(index, 0), steps.length - 1);

        steps.forEach((step, stepIndex) => {
            const active = stepIndex === currentStep;
            step.classList.toggle('is-active', active);
            step.hidden = !active;

            // Add slide animation
            if (active) {
                step.style.animation = 'fadeInUp 0.3s ease forwards';
            }
        });

        updateProgress();
    };

    const validateStep = () => {
        const activeStep = steps[currentStep];
        const fields = Array.from(activeStep.querySelectorAll('input, select, textarea'));

        return fields.every((field) => {
            if (field.disabled || field.type === 'hidden' || !field.willValidate) {
                return true;
            }

            const isValid = field.reportValidity();
            if (!isValid) {
                field.style.animation = 'shake 0.5s ease';
                setTimeout(() => field.style.animation = '', 500);
            }
            return isValid;
        });
    };

    form.querySelectorAll('[data-form-next]').forEach((button) => {
        button.addEventListener('click', () => {
            if (validateStep()) {
                showStep(currentStep + 1);
            }
        });
    });

    form.querySelectorAll('[data-form-prev]').forEach((button) => {
        button.addEventListener('click', () => showStep(currentStep - 1));
    });

    form.addEventListener('submit', (event) => {
        if (!validateStep()) {
            event.preventDefault();
        }
    });

    form.addEventListener('reset', () => showStep(0));
    showStep(currentStep);
});

// --- Enhanced Tab System ---
document.querySelectorAll('[data-tab-trigger]').forEach((trigger) => {
    trigger.addEventListener('click', () => {
        const key = trigger.dataset.tabTrigger;
        const tabContainer = trigger.closest('[data-tabs]');
        const triggers = tabContainer?.querySelectorAll('[data-tab-trigger]') || document.querySelectorAll('[data-tab-trigger]');
        const panels = tabContainer?.querySelectorAll('[data-tab-panel]') || document.querySelectorAll('[data-tab-panel]');

        triggers.forEach((item) => {
            const active = item === trigger;
            item.classList.toggle('is-active', active);
            item.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        panels.forEach((panel) => {
            const active = panel.dataset.tabPanel === key;
            panel.classList.toggle('is-active', active);
            panel.hidden = !active;

            if (active) {
                panel.style.animation = 'fadeInUp 0.3s ease forwards';
            }
        });
    });
});

// --- Enhanced Carousel ---
document.querySelectorAll('[data-carousel]').forEach((carousel) => {
    const track = carousel.querySelector('.testimonial-track, .carousel-track');
    const cards = Array.from(carousel.querySelectorAll('.testimonial-card, .carousel-card'));
    const prev = carousel.querySelector('[data-carousel-prev]');
    const next = carousel.querySelector('[data-carousel-next]');
    const indicators = carousel.querySelectorAll('[data-carousel-indicator]');

    if (!track || !cards.length) {
        return;
    }

    let index = 0;
    let timer;

    const perView = () => (window.innerWidth <= 768 ? 1 : window.innerWidth <= 1024 ? 2 : 3);
    const maxIndex = () => Math.max(cards.length - perView(), 0);

    const update = () => {
        const step = cards[0].getBoundingClientRect().width + 24; // gap
        track.style.transform = `translateX(-${index * step}px)`;
        track.style.transition = 'transform 0.3s ease';

        // Update indicators
        indicators.forEach((indicator, i) => {
            indicator.classList.toggle('is-active', i === Math.floor(index / perView()));
        });
    };

    const nextSlide = () => {
        index = index >= maxIndex() ? 0 : index + 1;
        update();
    };

    const prevSlide = () => {
        index = index <= 0 ? maxIndex() : index - 1;
        update();
    };

    const goToSlide = (slideIndex) => {
        index = Math.min(Math.max(slideIndex * perView(), 0), maxIndex());
        update();
    };

    // Event listeners
    if (next) next.addEventListener('click', nextSlide);
    if (prev) prev.addEventListener('click', prevSlide);

    indicators.forEach((indicator, i) => {
        indicator.addEventListener('click', () => goToSlide(i));
    });

    // Auto-play
    const startTimer = () => {
        timer = setInterval(nextSlide, 5000);
    };

    const stopTimer = () => {
        clearInterval(timer);
    };

    carousel.addEventListener('mouseenter', stopTimer);
    carousel.addEventListener('mouseleave', startTimer);

    // Touch/swipe support
    let startX = 0;
    let isDragging = false;

    track.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
        isDragging = true;
        stopTimer();
    });

    track.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        const currentX = e.touches[0].clientX;
        const diff = startX - currentX;

        if (Math.abs(diff) > 50) {
            if (diff > 0) nextSlide();
            else prevSlide();
            isDragging = false;
        }
    });

    track.addEventListener('touchend', () => {
        isDragging = false;
        startTimer();
    });

    // Keyboard navigation
    carousel.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') prevSlide();
        else if (e.key === 'ArrowRight') nextSlide();
    });

    carousel.setAttribute('tabindex', '0');

    update();
    startTimer();
});

// --- Enhanced Reveal Animations ---
const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
            const element = entry.target;
            const delay = element.dataset.revealDelay || 0;

            setTimeout(() => {
                element.classList.add('is-visible');
            }, delay);

            revealObserver.unobserve(element);
        }
    });
}, {
    threshold: 0.12,
    rootMargin: '0px 0px -60px 0px'
});

// Stagger animations for children
document.querySelectorAll('[data-stagger]').forEach((parent) => {
    Array.from(parent.children).forEach((child, i) => {
        if (!child.hasAttribute('data-reveal')) {
            child.setAttribute('data-reveal', '');
        }
        child.setAttribute('data-reveal-delay', Math.min(i * 100, 600).toString());
        revealObserver.observe(child);
    });
});

// Observe all reveal elements
document.querySelectorAll('[data-reveal], [data-animate], .filter-bar, .map-card, [data-carousel]').forEach((element) => {
    const dir = element.getAttribute('data-animate') || element.getAttribute('data-reveal');
    if (dir === 'left' && !element.hasAttribute('data-reveal')) element.setAttribute('data-reveal', 'left');
    else if (dir === 'right' && !element.hasAttribute('data-reveal')) element.setAttribute('data-reveal', 'right');
    else if (dir && !element.hasAttribute('data-reveal')) element.setAttribute('data-reveal', '');

    revealObserver.observe(element);
});

// --- Enhanced Loading States ---
const showLoading = (element) => {
    element.classList.add('loading');
};

const hideLoading = (element) => {
    element.classList.remove('loading');
};

// Apply to forms
document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', () => {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Processing...';
        }
    });
});

// --- Enhanced Modal System ---
document.querySelectorAll('[data-modal-trigger]').forEach((trigger) => {
    trigger.addEventListener('click', () => {
        const modalId = trigger.dataset.modalTrigger;
        const modal = document.getElementById(modalId);

        if (modal) {
            modal.showModal();
            document.body.style.overflow = 'hidden';

            // Focus trap
            const focusableElements = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            const handleTab = (e) => {
                if (e.key === 'Tab') {
                    if (e.shiftKey) {
                        if (document.activeElement === firstElement) {
                            lastElement.focus();
                            e.preventDefault();
                        }
                    } else {
                        if (document.activeElement === lastElement) {
                            firstElement.focus();
                            e.preventDefault();
                        }
                    }
                }
            };

            modal.addEventListener('keydown', handleTab);
            firstElement?.focus();

            // Close handlers
            modal.addEventListener('close', () => {
                document.body.style.overflow = '';
                modal.removeEventListener('keydown', handleTab);
            });
        }
    });
});

// Close modals on escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('dialog[open]').forEach((modal) => {
            modal.close();
        });
    }
});

// --- Enhanced Toast Notifications ---
const showToast = (message, type = 'info', duration = 5000) => {
    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    toast.innerHTML = `
        <div class="toast__content">
            <span class="toast__icon">${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}</span>
            <span class="toast__message">${message}</span>
        </div>
        <button class="toast__close" aria-label="Close notification">×</button>
    `;

    document.body.appendChild(toast);

    // Animate in
    setTimeout(() => toast.classList.add('is-visible'), 10);

    // Auto remove
    const removeToast = () => {
        toast.classList.remove('is-visible');
        setTimeout(() => toast.remove(), 300);
    };

    toast.querySelector('.toast__close').addEventListener('click', removeToast);

    if (duration > 0) {
        setTimeout(removeToast, duration);
    }
};

// Make toast function globally available
window.showToast = showToast;

// --- Enhanced Search and Filter ---
document.querySelectorAll('[data-search]').forEach((searchInput) => {
    const container = searchInput.closest('[data-search-container]');
    const items = container?.querySelectorAll('[data-search-item]') || [];

    searchInput.addEventListener('input', () => {
        const query = searchInput.value.toLowerCase();

        items.forEach((item) => {
            const text = item.textContent.toLowerCase();
            const matches = text.includes(query);
            item.style.display = matches ? '' : 'none';

            if (matches) {
                // Highlight matching text
                const regex = new RegExp(`(${query})`, 'gi');
                item.innerHTML = item.textContent.replace(regex, '<mark>$1</mark>');
            }
        });
    });
});

// --- Enhanced Dropdowns ---
document.querySelectorAll('[data-dropdown-trigger]').forEach((trigger) => {
    const dropdown = trigger.nextElementSibling;
    let isOpen = false;

    if (!dropdown?.hasAttribute('data-dropdown')) return;

    const toggleDropdown = () => {
        isOpen = !isOpen;
        dropdown.classList.toggle('is-open', isOpen);
        trigger.setAttribute('aria-expanded', isOpen);
    };

    const closeDropdown = () => {
        isOpen = false;
        dropdown.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
    };

    trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleDropdown();
    });

    // Close on outside click
    document.addEventListener('click', (e) => {
        if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
            closeDropdown();
        }
    });

    // Close on escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isOpen) {
            closeDropdown();
        }
    });

    // Handle dropdown item selection
    dropdown.querySelectorAll('[data-dropdown-item]').forEach((item) => {
        item.addEventListener('click', () => {
            const value = item.dataset.value;
            const text = item.textContent;

            // Update trigger text if needed
            if (trigger.querySelector('[data-dropdown-text]')) {
                trigger.querySelector('[data-dropdown-text]').textContent = text;
            }

            // Update hidden input if exists
            const hiddenInput = trigger.querySelector('input[type="hidden"]');
            if (hiddenInput) {
                hiddenInput.value = value;
            }

            closeDropdown();
        });
    });
});

// --- Performance Optimizations ---
let resizeTimeout;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        // Handle responsive updates
        document.querySelectorAll('[data-carousel]').forEach((carousel) => {
            // Reinitialize carousels on resize
            const event = new Event('resize');
            carousel.dispatchEvent(event);
        });
    }, 250);
});

// --- Accessibility Enhancements ---
document.addEventListener('DOMContentLoaded', () => {
    // Add skip links
    const skipLink = document.querySelector('.skip-link');
    if (skipLink) {
        skipLink.addEventListener('click', (e) => {
            e.preventDefault();
            const target = document.querySelector(skipLink.getAttribute('href'));
            if (target) {
                target.focus();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

    // Enhanced focus management
    document.querySelectorAll('button, a, input, select, textarea').forEach((element) => {
        element.addEventListener('focus', () => {
            element.style.outline = '2px solid var(--color-primary)';
            element.style.outlineOffset = '2px';
        });

        element.addEventListener('blur', () => {
            element.style.outline = '';
            element.style.outlineOffset = '';
        });
    });
});

// --- Error Handling ---
window.addEventListener('error', (e) => {
    console.error('JavaScript error:', e.error);
    showToast('Something went wrong. Please try again.', 'error');
});

window.addEventListener('unhandledrejection', (e) => {
    console.error('Unhandled promise rejection:', e.reason);
    showToast('Something went wrong. Please try again.', 'error');
});

// --- Initialize Google Maps (if present) ---
window.initHomeMap = function() {
    if (typeof google === 'undefined' || !google.maps) {
        console.warn('Google Maps not loaded');
        return;
    }

    const mapElement = document.getElementById('homeMap');
    if (!mapElement) return;

    const defaultCenter = { lat: 39.8283, lng: -98.5795 }; // Center of US

    const map = new google.maps.Map(mapElement, {
        zoom: 4,
        center: defaultCenter,
        styles: [
            {
                featureType: 'water',
                elementType: 'geometry',
                stylers: [{ color: '#e9e9e9' }, { lightness: 17 }]
            },
            {
                featureType: 'landscape',
                elementType: 'geometry',
                stylers: [{ color: '#f5f5f5' }, { lightness: 20 }]
            }
        ],
        disableDefaultUI: true,
        zoomControl: true,
        zoomControlOptions: {
            position: google.maps.ControlPosition.RIGHT_CENTER
        }
    });

    let marker = null;
    let geocoder = new google.maps.Geocoder();

    // Function to geocode ZIP and update map
    window.geocodeZIP = function(zipCode) {
        if (!zipCode || zipCode.length < 5) return;

        geocoder.geocode({ address: zipCode }, (results, status) => {
            if (status === 'OK' && results[0]) {
                const location = results[0].geometry.location;

                map.setCenter(location);
                map.setZoom(10);

                if (marker) {
                    marker.setPosition(location);
                } else {
                    marker = new google.maps.Marker({
                        position: location,
                        map: map,
                        animation: google.maps.Animation.DROP
                    });
                }

                // Add info window
                const infoWindow = new google.maps.InfoWindow({
                    content: `<div style="padding: 8px;"><strong>${zipCode}</strong><br>Service area available</div>`
                });

                marker.addListener('click', () => {
                    infoWindow.open(map, marker);
                });

                // Auto-open info window
                setTimeout(() => infoWindow.open(map, marker), 1000);
            }
        });
    };

    // Listen for ZIP input changes
    const zipInput = document.querySelector('input[name="zip_code"]');
    if (zipInput) {
        let timeout;
        zipInput.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                window.geocodeZIP(zipInput.value);
            }, 500);
        });
    }
};

// --- Legacy Functions (preserved for compatibility) ---
const parseMoneyValue = (rawValue) => {
    const normalized = String(rawValue || '')
        .toLowerCase()
        .replace(/[$,\s]/g, '')
        .trim();

    if (!normalized) {
        return null;
    }

    let multiplier = 1;
    let numericValue = normalized;

    if (normalized.endsWith('m')) {
        multiplier = 1000000;
        numericValue = normalized.slice(0, -1);
    } else if (normalized.endsWith('k')) {
        multiplier = 1000;
        numericValue = normalized.slice(0, -1);
    }

    const parsed = Number.parseFloat(numericValue);
    return Number.isNaN(parsed) ? null : parsed * multiplier;
};

const parsePriceRange = (value) => {
    if (!value) {
        return [0, Number.MAX_SAFE_INTEGER];
    }

    const matches = String(value).toLowerCase().match(/\d+(?:\.\d+)?\s*[mk]?/g) || [];
    const numbers = matches
        .map((match) => parseMoneyValue(match))
        .filter((match) => match)
        .sort((a, b) => a - b);

    if (!numbers.length) {
        return [0, Number.MAX_SAFE_INTEGER];
    }

    if (numbers.length === 1) {
        return [0, numbers[0]];
    }

    return [numbers[0], numbers[numbers.length - 1]];
};

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    // Initialize AOS if available
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-out-cubic',
            once: true,
            offset: 50
        });
    }

    // Initialize maps if available
    if (window.initHomeMap) {
        window.initHomeMap();
    }

    // Dashboard loading state
    if (document.body.classList.contains('page-dashboard')) {
        document.body.classList.add('dashboard-loading');
        const dropDashboardSkeleton = () => document.body.classList.remove('dashboard-loading');
        window.addEventListener('load', dropDashboardSkeleton);
        setTimeout(dropDashboardSkeleton, 1200);
    }

    // Pricing toggle
    const pricingToggle = document.getElementById('pricingToggle');
    const pricingSaveBadge = document.getElementById('pricingSaveBadge');
    const oneTimeLabel = document.getElementById('toggle-onetime-label');
    const monthlyLabel = document.getElementById('toggle-monthly-label');

    if (pricingToggle) {
        const updatePricingToggle = () => {
            const monthly = pricingToggle.classList.toggle('is-monthly');
            pricingToggle.setAttribute('aria-checked', monthly ? 'true' : 'false');
            oneTimeLabel?.classList.toggle('is-active', !monthly);
            monthlyLabel?.classList.toggle('is-active', monthly);
            pricingSaveBadge?.classList.toggle('is-visible', monthly);

            document.querySelectorAll('[id^="price-onetime-"]').forEach((row) => {
                row.style.display = monthly ? 'none' : 'flex';
            });
            document.querySelectorAll('[id^="price-monthly-"]').forEach((row) => {
                row.style.display = monthly ? 'flex' : 'none';
            });
        };

        pricingToggle.addEventListener('click', updatePricingToggle);
        pricingToggle.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                updatePricingToggle();
            }
        });
    }

    // Package filtering
    const pricingCategoryButtons = Array.from(document.querySelectorAll('[data-pricing-category]'));
    const pricingPanels = Array.from(document.querySelectorAll('[data-pricing-panel]'));
    const packageSearch = document.getElementById('packageSearch');
    const packageFilterButtons = Array.from(document.querySelectorAll('[data-package-filter]'));
    const packageResultsSummary = document.getElementById('packageResultsSummary');
    let activePricingCategory = pricingCategoryButtons.find((button) => button.classList.contains('is-active'))?.dataset.pricingCategory || 'leads';
    let activePackageFilter = 'all';

    const getActivePricingPanel = () => pricingPanels.find((panel) => panel.dataset.pricingPanel === activePricingCategory);

    const applyPackageFilters = () => {
        const activePanel = getActivePricingPanel();
        if (!activePanel) {
            return;
        }

        const query = (packageSearch?.value || '').trim().toLowerCase();
        const cards = Array.from(activePanel.querySelectorAll('[data-package-card]'));
        const emptyState = activePanel.querySelector('[data-package-empty]');
        let visibleCount = 0;

        cards.forEach((card) => {
            const haystack = `${card.dataset.packageName || ''} ${card.dataset.packageFeatures || ''}`;
            const matchesSearch = !query || haystack.includes(query);
            const matchesFilter = activePackageFilter === 'all'
                || (activePackageFilter === 'featured' && card.dataset.packageFeatured === 'true')
                || (activePackageFilter === 'support' && card.dataset.packageSupport === 'true');
            const visible = matchesSearch && matchesFilter;

            card.hidden = !visible;
            card.style.display = visible ? 'flex' : 'none';
            if (visible) {
                visibleCount += 1;
            }
        });

        pricingPanels
            .filter((panel) => panel !== activePanel)
            .forEach((panel) => {
                panel.querySelectorAll('[data-package-card]').forEach((card) => {
                    card.hidden = false;
                    card.style.display = 'flex';
                });
                panel.querySelector('[data-package-empty]')?.setAttribute('hidden', 'hidden');
            });

        if (emptyState) {
            emptyState.hidden = visibleCount !== 0;
        }

        if (packageResultsSummary) {
            packageResultsSummary.textContent = `${visibleCount} package${visibleCount === 1 ? '' : 's'} match your current filters.`;
        }
    };

    pricingCategoryButtons.forEach((button) => {
        button.addEventListener('click', () => {
            activePricingCategory = button.dataset.pricingCategory || 'leads';
            pricingCategoryButtons.forEach((item) => {
                const active = item === button;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            pricingPanels.forEach((panel) => {
                const active = panel.dataset.pricingPanel === activePricingCategory;
                panel.hidden = !active;
                panel.style.display = active ? '' : 'none';
            });
            applyPackageFilters();
        });
    });

    packageFilterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            activePackageFilter = button.dataset.packageFilter || 'all';
            packageFilterButtons.forEach((item) => item.classList.toggle('is-active', item === button));
            applyPackageFilters();
        });
    });

    packageSearch?.addEventListener('input', applyPackageFilters);
    if (pricingPanels.length) {
        applyPackageFilters();
    }

    // VA Addon toggle
    const vaAddonToggle = document.getElementById('vaAddonToggle');
    const vaAddonRail = document.getElementById('vaAddonRail');

    if (vaAddonToggle && vaAddonRail) {
        const syncVaAddonRail = () => {
            const visible = vaAddonToggle.checked && activePricingCategory === 'leads';
            vaAddonRail.hidden = !visible;
            vaAddonRail.classList.toggle('is-visible', visible);
        };

        vaAddonToggle.addEventListener('change', syncVaAddonRail);
        pricingCategoryButtons.forEach((button) => {
            button.addEventListener('click', syncVaAddonRail);
        });
        syncVaAddonRail();
    }

    // Package modal
    const packageModal = document.getElementById('packageModal');
    const packageModalFrame = document.getElementById('packageModalFrame');
    const packageModalTitle = document.getElementById('packageModalTitle');
    const packageModalDescription = document.getElementById('packageModalDescription');
    const packageModalOnboarding = document.getElementById('packageModalOnboarding');
    const packageModalStripeCheckout = document.getElementById('packageModalStripeCheckout');
    const packageModalStatus = document.getElementById('packageModalStatus');
    const packageModalClose = document.getElementById('packageModalClose');
    const packageModalCancel = document.getElementById('packageModalCancel');

    if (packageModal && packageModalFrame && packageModalTitle && packageModalDescription && packageModalOnboarding) {
        const defaultOnboardingHref = packageModalOnboarding.getAttribute('href') || '';
        const defaultCheckoutHref = packageModalStripeCheckout?.getAttribute('href') || '';
        const defaultStatus = packageModalStatus?.textContent?.trim() || 'Complete the package form and payment to unlock onboarding.';
        let packageSubmissionComplete = false;

        const setOnboardingState = (completed) => {
            packageSubmissionComplete = completed;
            packageModalOnboarding.hidden = !completed;
            packageModalOnboarding.setAttribute('aria-hidden', completed ? 'false' : 'true');
            packageModalOnboarding.setAttribute('aria-disabled', completed ? 'false' : 'true');
            packageModalOnboarding.tabIndex = completed ? 0 : -1;

            if (packageModalStatus) {
                packageModalStatus.textContent = completed
                    ? 'Payment confirmed. You can continue to onboarding now.'
                    : defaultStatus;
            }
        };

        const resetPackageModal = () => {
            packageModal.hidden = true;
            packageModal.setAttribute('aria-hidden', 'true');
            packageModalFrame.src = 'about:blank';
            packageModalOnboarding.href = defaultOnboardingHref;
            if (packageModalStripeCheckout) {
                packageModalStripeCheckout.href = defaultCheckoutHref;
            }
            document.body.classList.remove('modal-open');
            setOnboardingState(false);
        };

        const openModal = (trigger) => {
            packageModalTitle.textContent = trigger.dataset.packageTitle || 'Complete your package selection';
            packageModalDescription.textContent = trigger.dataset.packageDescription || 'Finish the package form to move into onboarding.';
            packageModalOnboarding.href = trigger.dataset.packageOnboarding || defaultOnboardingHref;
            if (packageModalStripeCheckout) {
                packageModalStripeCheckout.href = trigger.dataset.packageCheckout || defaultCheckoutHref;
            }
            setOnboardingState(false);
            packageModalFrame.src = trigger.dataset.packageSrc || 'about:blank';
            packageModal.hidden = false;
            packageModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
        };

        const closeModal = () => resetPackageModal();

        resetPackageModal();
        window.addEventListener('pageshow', resetPackageModal);

        packageModalOnboarding.addEventListener('click', (event) => {
            if (!packageSubmissionComplete) {
                event.preventDefault();
            }
        });

        document.querySelectorAll('[data-package-modal-open]').forEach((trigger) => {
            trigger.addEventListener('click', (event) => {
                if (trigger.dataset.packageSrc) {
                    event.preventDefault();
                    openModal(trigger);
                }
            });
        });

        packageModalClose?.addEventListener('click', closeModal);
        packageModalCancel?.addEventListener('click', closeModal);
        packageModal.addEventListener('click', (event) => {
            if (event.target === packageModal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !packageModal.hidden) {
                closeModal();
            }
        });

        packageModal._setOnboardingState = setOnboardingState;
    }

    const submissionLooksComplete = (payload) => {
        if (!payload) {
            return false;
        }

        const serialized = typeof payload === 'string' ? payload.toLowerCase() : JSON.stringify(payload).toLowerCase();

        return [
            'submitted',
            'form_submitted',
            'formsubmitted',
            'survey_submitted',
            'surveysubmitted',
            'thank_you',
            'thankyou',
            'success',
            'completed',
        ].some((keyword) => serialized.includes(keyword));
    };

    const initHomeMap = () => {
        if (!document.body.classList.contains('page-home')) {
            return;
        }

        const mapContainer = document.getElementById('hero-map');
        if (!mapContainer) {
            return;
        }

        if (!window.google?.maps) {
            if (window.initHomeMapLoadingStarted) {
                return;
            }

            window.initHomeMapLoadingStarted = true;

            const apiKey = document.body.dataset.googleMapsApiKey || '';
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&libraries=places&callback=initHomeMap`;
            script.defer = true;
            script.async = true;
            document.body.appendChild(script);
            return;
        }

        const defaultLocation = { lat: 39.8283, lng: -98.5795 };

        const map = new window.google.maps.Map(mapContainer, {
            center: defaultLocation,
            zoom: 4,
            disableDefaultUI: true,
            styles: [
                { featureType: 'poi', stylers: [{ visibility: 'off' }] },
                { featureType: 'transit', stylers: [{ visibility: 'off' }] },
                { featureType: 'water', stylers: [{ color: '#a8d4f7' }] },
            ],
        });

        const marker = new window.google.maps.Marker({
            map,
            position: defaultLocation,
            icon: {
                path: 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z',
                fillColor: '#FF6B00',
                fillOpacity: 1,
                strokeColor: '#fff',
                strokeWeight: 2,
                scale: 1.8,
                anchor: new window.google.maps.Point(12, 24),
            },
        });

        const geocoder = new window.google.maps.Geocoder();

        const updateZipInfo = (zip) => {
            const zipDisplay = document.getElementById('buyer-zip-display');
            const zipStatus = document.getElementById('buyer-zip-status');
            if (zipDisplay) zipDisplay.textContent = zip || 'Enter ZIP';
            if (zipStatus) zipStatus.textContent = zip ? 'Searching...' : 'Awaiting input';

            if (!zip || zip.length < 5) {
                if (zipStatus) zipStatus.textContent = 'Enter a 5-digit ZIP code';
                return;
            }

            geocoder.geocode({ address: zip }, (results, status) => {
                if (status === 'OK' && results?.[0]) {
                    const position = results[0].geometry.location;
                    marker.setPosition(position);
                    map.panTo(position);
                    map.setZoom(11);

                    if (zipStatus) {
                        zipStatus.textContent = results[0].formatted_address;
                    }
                } else {
                    if (zipStatus) {
                        zipStatus.textContent = 'Unable to find this ZIP code';
                    }
                }
            });
        };

        const zipInputSelectors = ['input[name="zip_code"]'];
        zipInputSelectors.forEach((selector) => {
            document.querySelectorAll(selector).forEach((input) => {
                input.addEventListener('input', (event) => {
                    const value = String(event.target.value || '').trim();
                    const sanitized = value.replace(/[^0-9]/g, '');
                    if (event.target.value !== sanitized) {
                        event.target.value = sanitized;
                    }
                    updateZipInfo(sanitized);
                });
            });
        });

        setTimeout(() => mapContainer.classList.add('is-loaded'), 250);
    };

    const statItems = document.querySelectorAll('.stat-strip__item[data-counter]');
    if (statItems.length) {
        const countUp = (el) => {
            const target = parseInt(el.dataset.counter, 10);
            const suffix = el.dataset.suffix || '';
            const numberEl = el.querySelector('.stat-strip__number');
            if (!numberEl || Number.isNaN(target)) return;
            const duration = 1800;
            const start = performance.now();
            const animate = (now) => {
                const elapsed = now - start;
                const progress = Math.min(elapsed / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = Math.round(eased * target);
                numberEl.textContent = current.toLocaleString() + suffix;
                if (progress < 1) {
                    requestAnimationFrame(animate);
                }
            };
            requestAnimationFrame(animate);
        };


        const statObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting && !entry.target.dataset.counted) {
                    entry.target.dataset.counted = 'true';
                    countUp(entry.target);
                }
            });
        }, { threshold: 0.5 });

        statItems.forEach((item) => statObserver.observe(item));
    }

    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        const showError = (fieldId, errorId, show) => {
            const field = document.getElementById(fieldId);
            const error = document.getElementById(errorId);
            if (!field || !error) return;
            error.style.display = show ? 'block' : 'none';
            field.style.borderColor = show ? '#dc2626' : '';
        };

        const validateEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

        contactForm.addEventListener('submit', (event) => {
            let valid = true;
            const name = document.getElementById('contactName');
            const email = document.getElementById('contactEmail');
            const message = document.getElementById('contactMessage');

            if (!name || !name.value.trim()) { showError('contactName', 'nameError', true); valid = false; } else { showError('contactName', 'nameError', false); }
            if (!email || !validateEmail(email.value)) { showError('contactEmail', 'emailError', true); valid = false; } else { showError('contactEmail', 'emailError', false); }
            if (!message || !message.value.trim()) { showError('contactMessage', 'messageError', true); valid = false; } else { showError('contactMessage', 'messageError', false); }

            if (!valid) {
                event.preventDefault();
                contactForm.querySelector('.field-error[style*="block"]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            const successBox = document.getElementById('contactSuccess');
            if (successBox) {
                event.preventDefault();
                contactForm.style.display = 'none';
                successBox.style.display = 'block';
                successBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                fetch(contactForm.action, { method: 'POST', body: new FormData(contactForm) });
            }
        });

        ['contactName', 'contactEmail', 'contactMessage'].forEach((id) => {
            const el = document.getElementById(id);
            el?.addEventListener('input', () => {
                const errorMap = { contactName: 'nameError', contactEmail: 'emailError', contactMessage: 'messageError' };
                showError(id, errorMap[id], false);
            });
        });
    }

    const dashboardSwitch = document.querySelector('[data-dashboard-switch]');
    const onboardingCard = document.querySelector('[data-onboarding-embed]');
    const onboardingContinueButton = document.getElementById('onboardingContinueButton');

    if (dashboardSwitch && onboardingCard) {
        dashboardSwitch.querySelectorAll('[data-dashboard-target]').forEach((button) => {
            button.addEventListener('click', () => {
                dashboardSwitch.querySelectorAll('[data-dashboard-target]').forEach((item) => item.classList.remove('is-active'));
                button.classList.add('is-active');
                onboardingCard.dataset.dashboardUrl = button.dataset.dashboardTarget || onboardingCard.dataset.dashboardUrl;
                if (onboardingContinueButton) {
                    onboardingContinueButton.href = onboardingCard.dataset.dashboardUrl;
                }
            });
        });
    }

    window.addEventListener('message', (event) => {
        const origin = event.origin || '';
        const trusted = origin.includes('leadconnectorhq.com') || origin.includes('msgsndr.com');

        if (!trusted || !submissionLooksComplete(event.data)) {
            return;
        }

        const activeOnboardingCard = document.querySelector('[data-onboarding-embed]');
        const dashboardUrl = activeOnboardingCard?.dataset.dashboardUrl;

        if (activeOnboardingCard && dashboardUrl) {
            window.location.href = dashboardUrl;
            return;
        }

        if (packageModal && !packageModal.hidden && typeof packageModal._setOnboardingState === 'function') {
            packageModal._setOnboardingState(true);
        }
    });

    const listingsForm = document.getElementById('listingsFilterForm');
    if (listingsForm) {
        const zipInput = document.getElementById('filterZip');
        const typeInput = document.getElementById('filterType');
        const priceInput = document.getElementById('filterPrice');
        const applyBtn = document.getElementById('filterApply');
        const resetBtn = document.getElementById('filterReset');
        const saveBtn = document.getElementById('filterSaveSearch');
        const sortInput = document.getElementById('listingSort');
        const resultsCount = document.getElementById('listingResultsCount');
        const emptyState = document.getElementById('listingEmptyState');
        const mapFilterChip = document.getElementById('mapFilterChip');
        const cards = Array.from(document.querySelectorAll('[data-listing-card]'));
        const defaultMapSrc = mapFrame?.getAttribute('src') || '';
        const storageKey = 'omnireferral:listings-filters';

        const renderCards = () => {
            const zipValue = (zipInput?.value || '').trim().toLowerCase();
            const typeValue = (typeInput?.value || '').trim().toLowerCase();
            const [minPrice, maxPrice] = parsePriceRange(priceInput?.value || '');
            const sortValue = sortInput?.value || 'relevant';

            const filteredCards = cards.filter((card) => {
                const cardZip = (card.dataset.zip || '').toLowerCase();
                const cardType = (card.dataset.type || '').toLowerCase();
                const cardPrice = parseInt(card.dataset.price || '0', 10);
                const zipMatch = !zipValue || cardZip.includes(zipValue);
                const typeMatch = !typeValue || cardType === typeValue;
                const priceMatch = cardPrice >= minPrice && cardPrice <= maxPrice;

                return zipMatch && typeMatch && priceMatch;
            });

            filteredCards.sort((a, b) => {
                const aPrice = parseInt(a.dataset.price || '0', 10);
                const bPrice = parseInt(b.dataset.price || '0', 10);
                const aType = a.dataset.type || '';
                const bType = b.dataset.type || '';
                const aOrder = parseInt(a.dataset.order || '0', 10);
                const bOrder = parseInt(b.dataset.order || '0', 10);

                if (sortValue === 'price-asc') return aPrice - bPrice;
                if (sortValue === 'price-desc') return bPrice - aPrice;
                if (sortValue === 'type') return aType.localeCompare(bType);
                return aOrder - bOrder;
            });

            cards.forEach((card) => {
                card.hidden = true;
                card.style.display = 'none';
            });

            filteredCards.forEach((card) => {
                card.hidden = false;
                card.style.display = 'flex';
                listingGrid?.appendChild(card);
            });

            if (resultsCount) {
                resultsCount.textContent = String(filteredCards.length);
            }
            if (emptyState) {
                emptyState.hidden = filteredCards.length !== 0;
            }
            if (mapFrame) {
                const newSrc = zipValue
                    ? `https://www.google.com/maps?q=${encodeURIComponent(zipValue)}&output=embed`
                    : defaultMapSrc;
                mapFrame.src = newSrc;
                if (listingsMapPanelFrame) {
                    listingsMapPanelFrame.src = newSrc;
                }
            }
            if (mapFilterChip) {
                mapFilterChip.textContent = zipValue ? `ZIP ${zipValue.toUpperCase()}` : 'Map Ready';
            }
        };

        const saveCurrentFilters = () => {
            const payload = {
                zip: zipInput?.value || '',
                type: typeInput?.value || '',
                price: priceInput?.value || '',
                sort: sortInput?.value || 'relevant',
            };
            localStorage.setItem(storageKey, JSON.stringify(payload));
            if (saveBtn) {
                const previousLabel = saveBtn.textContent;
                saveBtn.textContent = 'Search Saved';
                window.setTimeout(() => {
                    saveBtn.textContent = previousLabel;
                }, 1400);
            }
        };

        try {
            const savedFilters = JSON.parse(localStorage.getItem(storageKey) || 'null');
            if (savedFilters) {
                if (zipInput) zipInput.value = savedFilters.zip || '';
                if (typeInput) typeInput.value = savedFilters.type || '';
                if (priceInput) priceInput.value = savedFilters.price || '';
                if (sortInput) sortInput.value = savedFilters.sort || 'relevant';
            }
        } catch (error) {
            localStorage.removeItem(storageKey);
        }

        ['input', 'change'].forEach((eventName) => {
            zipInput?.addEventListener(eventName, renderCards);
            typeInput?.addEventListener(eventName, renderCards);
            priceInput?.addEventListener(eventName, renderCards);
            sortInput?.addEventListener(eventName, renderCards);
        });
        applyBtn?.addEventListener('click', renderCards);
        resetBtn?.addEventListener('click', () => {
            listingsForm.reset();
            if (sortInput) {
                sortInput.value = 'relevant';
            }
            localStorage.removeItem(storageKey);
            renderCards();
        });
        saveBtn?.addEventListener('click', saveCurrentFilters);
        renderCards();
    }

    const listingsMapPanelFrame = document.getElementById('listingsMapPanelFrame');
    const listingGrid = document.getElementById('listingGrid');
    const mapFrame = document.getElementById('listingsMapFrame');
    const listingsViewButtons = Array.from(document.querySelectorAll('[data-view-toggle]'));
    const listingsMapPanel = document.getElementById('listingsMapPanel');
    const listingsMainWrapper = document.querySelector('.listings-main');

    const setListingsViewMode = (mode) => {
        const showMap = mode === 'map';
        if (listingGrid) {
            listingGrid.hidden = showMap;
            listingGrid.style.display = showMap ? 'none' : '';
        }
        if (listingsMapPanel) {
            listingsMapPanel.hidden = !showMap;
        }
        listingsViewButtons.forEach((button) => {
            const active = button.dataset.viewToggle === mode;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', String(active));
        });
        if (listingsMainWrapper) {
            listingsMainWrapper.classList.toggle('listings-main--map-view', showMap);
        }
        if (listingsMapPanelFrame && mapFrame) {
            listingsMapPanelFrame.src = mapFrame.src;
        }
    };

    listingsViewButtons.forEach((button) => {
        button.addEventListener('click', () => setListingsViewMode(button.dataset.viewToggle || 'list'));
    });

    setListingsViewMode('list');

    const agentDirectoryGrid = document.getElementById('agentDirectoryGrid');
    if (agentDirectoryGrid) {
        const agentCityFilter = document.getElementById('agentCityFilter');
        const agentSpecialtyFilter = document.getElementById('agentSpecialtyFilter');
        const agentCards = Array.from(document.querySelectorAll('[data-agent-card]'));

        const applyAgentFilters = () => {
            const cityValue = (agentCityFilter?.value || '').trim().toLowerCase();
            const specialtyValue = (agentSpecialtyFilter?.value || '').trim().toLowerCase();
            agentCards.forEach((card) => {
                const matchesCity = !cityValue || (card.dataset.city || '').toLowerCase().includes(cityValue);
                const matchesSpecialty = !specialtyValue || (card.dataset.specialty || '').toLowerCase().includes(specialtyValue);
                const visible = matchesCity && matchesSpecialty;
                card.hidden = !visible;
                card.style.display = visible ? '' : 'none';
            });
        };

        agentCityFilter?.addEventListener('change', applyAgentFilters);
        agentSpecialtyFilter?.addEventListener('change', applyAgentFilters);
        applyAgentFilters();
    }

    const authRoleTabs = Array.from(document.querySelectorAll('[data-role-tab]'));
    const authRoleSelect = document.getElementById('loginRoleSelect') || document.getElementById('registerRoleSelect');
    if (authRoleTabs.length && authRoleSelect) {
        const syncRoleTabState = (category) => {
            authRoleTabs.forEach((tab) => {
                const active = tab.dataset.roleTab === category;
                tab.classList.toggle('is-active', active);
            });
            Array.from(authRoleSelect.options).forEach((option) => {
                const optionCategory = option.dataset.roleGroup || '';
                option.hidden = optionCategory !== category;
                option.disabled = optionCategory !== category;
            });
            const selectedOption = Array.from(authRoleSelect.options).find((option) => !option.disabled);
            if (selectedOption) {
                selectedOption.selected = true;
            }
        };

        authRoleTabs.forEach((tab) => {
            tab.addEventListener('click', () => syncRoleTabState(tab.dataset.roleTab));
        });

        const initialCategory = authRoleSelect.selectedOptions[0]?.dataset.roleGroup || 'buyer';
        syncRoleTabState(initialCategory);
    }

    const buyerDashboardZipFilter = document.getElementById('buyerDashboardZipFilter');
    if (buyerDashboardZipFilter) {
        const buyerDashboardSearchButton = document.getElementById('buyerDashboardSearchButton');
        const buyerDashboardResultsSummary = document.getElementById('buyerDashboardResultsSummary');
        const buyerDashboardEmptyState = document.getElementById('buyerDashboardEmptyState');
        const buyerDashboardMapFrame = document.getElementById('buyerDashboardMapFrame');
        const buyerDashboardListingGrid = document.getElementById('buyerDashboardListingGrid');
        const buyerCards = Array.from(document.querySelectorAll('[data-buyer-listing-card]'));
        const defaultBuyerMapSrc = buyerDashboardMapFrame?.getAttribute('src') || '';

        const renderBuyerDashboardListings = () => {
            const zipValue = buyerDashboardZipFilter.value.trim().toLowerCase();
            const filtered = buyerCards.filter((card) => !zipValue || (card.dataset.zip || '').toLowerCase().includes(zipValue));

            buyerCards.forEach((card) => {
                card.hidden = true;
                card.style.display = 'none';
            });

            filtered
                .sort((a, b) => parseInt(a.dataset.order || '0', 10) - parseInt(b.dataset.order || '0', 10))
                .forEach((card) => {
                    card.hidden = false;
                    card.style.display = '';
                    buyerDashboardListingGrid?.appendChild(card);
                });

            if (buyerDashboardResultsSummary) {
                buyerDashboardResultsSummary.textContent = zipValue
                    ? `${filtered.length} saved home${filtered.length === 1 ? '' : 's'} in or near ZIP ${zipValue.toUpperCase()}.`
                    : `Showing ${filtered.length} saved home${filtered.length === 1 ? '' : 's'}.`;
            }
            if (buyerDashboardEmptyState) {
                buyerDashboardEmptyState.hidden = filtered.length !== 0;
            }
            if (buyerDashboardMapFrame) {
                buyerDashboardMapFrame.src = zipValue
                    ? `https://www.google.com/maps?q=${encodeURIComponent(zipValue)}&output=embed`
                    : defaultBuyerMapSrc;
            }
        };

        buyerDashboardZipFilter.addEventListener('input', renderBuyerDashboardListings);
        buyerDashboardSearchButton?.addEventListener('click', renderBuyerDashboardListings);
        renderBuyerDashboardListings();
    }

    const validationForms = document.querySelectorAll('.auth-form-shell, .hero-form, .contact-form');
    const updateFieldState = (field) => {
        if (!field || !field.willValidate || field.type === 'hidden' || field.type === 'file') {
            return;
        }

        const value = typeof field.value === 'string' ? field.value.trim() : field.value;
        if (!value) {
            field.classList.remove('is-valid', 'is-invalid');
            return;
        }

        const valid = field.checkValidity();
        field.classList.toggle('is-valid', valid);
        field.classList.toggle('is-invalid', !valid);
    };

    validationForms.forEach((form) => {
        form.querySelectorAll('input, select, textarea').forEach((field) => {
            field.addEventListener('blur', () => updateFieldState(field));
            field.addEventListener('input', () => updateFieldState(field));
            field.addEventListener('change', () => updateFieldState(field));
        });

        if (form.classList.contains('auth-form-shell')) {
            form.addEventListener('submit', (event) => {
                const fields = Array.from(form.querySelectorAll('input, select, textarea')).filter((field) => field.willValidate && field.type !== 'hidden' && field.type !== 'file');
                fields.forEach(updateFieldState);
                const firstInvalid = fields.find((field) => !field.checkValidity());
                if (firstInvalid) {
                    event.preventDefault();
                    firstInvalid.focus();
                }
            });
        }
    });

    document.querySelectorAll('img').forEach((img) => {
        if (!img.hasAttribute('loading')) {
            img.setAttribute('loading', 'lazy');
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const parseMoneyValue = (rawValue) => {
        const normalized = String(rawValue || '')
            .toLowerCase()
            .replace(/[$,\s]/g, '')
            .trim();

        if (!normalized) {
            return null;
        }

        let multiplier = 1;
        let numericValue = normalized;

        if (normalized.endsWith('m')) {
            multiplier = 1000000;
            numericValue = normalized.slice(0, -1);
        } else if (normalized.endsWith('k')) {
            multiplier = 1000;
            numericValue = normalized.slice(0, -1);
        }

        const parsed = Number.parseFloat(numericValue);
        return Number.isNaN(parsed) ? null : parsed * multiplier;
    };

    const parsePriceRange = (value) => {
        if (!value) {
            return [0, Number.MAX_SAFE_INTEGER];
        }

        const matches = String(value).toLowerCase().match(/\d+(?:\.\d+)?\s*[mk]?/g) || [];
        const numbers = matches
            .map((match) => parseMoneyValue(match))
            .filter((match) => typeof match === 'number' && !Number.isNaN(match));

        if (!numbers.length) {
            return [0, Number.MAX_SAFE_INTEGER];
        }

        if (numbers.length === 1) {
            return [0, numbers[0]];
        }

        return [Math.min(numbers[0], numbers[1]), Math.max(numbers[0], numbers[1])];
    };

    const menuToggle = document.getElementById('menuToggle');
    const mainNav = document.getElementById('mainNav');

    if (menuToggle && mainNav) {
        const syncMenuState = (open) => {
            mainNav.classList.toggle('is-open', open);
            menuToggle.classList.toggle('is-open', open);
            menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        };

        menuToggle.addEventListener('click', () => syncMenuState(!mainNav.classList.contains('is-open')));
        mainNav.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => syncMenuState(false)));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                syncMenuState(false);
            }
        });
        window.addEventListener('resize', () => {
            if (window.innerWidth > 1024) {
                syncMenuState(false);
            }
        });
    }

    const siteHeader = document.getElementById('siteHeader');
    const navSectionLinks = Array.from(document.querySelectorAll('.main-nav a[data-nav-section]'));

    const updateHeaderState = () => {
        siteHeader?.classList.toggle('is-scrolled', window.scrollY > 12);
    };

    const scrollToHashTarget = (hash) => {
        if (!hash || hash === '#') {
            return;
        }

        const target = document.querySelector(hash);
        if (!target) {
            return;
        }

        const offset = (siteHeader?.offsetHeight || 0) + 16;
        const top = target.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top, behavior: 'smooth' });
    };

    document.querySelectorAll('a[href*="#"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const href = link.getAttribute('href') || '';
            if (!href.includes('#')) {
                return;
            }

            const url = new URL(href, window.location.href);
            const samePage = url.pathname === window.location.pathname;
            const hash = url.hash;

            if (samePage && hash) {
                event.preventDefault();
                scrollToHashTarget(hash);
                if (menuToggle && mainNav?.classList.contains('is-open')) {
                    mainNav.classList.remove('is-open');
                    menuToggle.classList.remove('is-open');
                    menuToggle.setAttribute('aria-expanded', 'false');
                }
            }
        });
    });

    if (document.body.classList.contains('page-home') && navSectionLinks.length) {
        const sectionTargets = navSectionLinks
            .map((link) => ({
                link,
                section: document.getElementById(link.dataset.navSection || ''),
            }))
            .filter((item) => item.section);

        const activeNavObserver = new IntersectionObserver((entries) => {
            const visible = entries
                .filter((entry) => entry.isIntersecting)
                .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

            if (!visible) {
                return;
            }

            sectionTargets.forEach(({ link, section }) => {
                const active = section === visible.target;
                link.classList.toggle('is-active', active);
                link.setAttribute('aria-current', active ? 'page' : 'false');
            });
        }, { threshold: 0.35, rootMargin: '-120px 0px -45% 0px' });

        sectionTargets.forEach(({ section }) => activeNavObserver.observe(section));
    }

    updateHeaderState();
    window.addEventListener('scroll', updateHeaderState, { passive: true });

    window.initHomeMap = initHomeMap;
    initHomeMap();

    const tabTriggers = document.querySelectorAll('[data-tab-trigger]');
    const tabPanels = document.querySelectorAll('[data-tab-panel]');

    tabTriggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const key = trigger.dataset.tabTrigger;
            tabTriggers.forEach((item) => {
                const active = item === trigger;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            tabPanels.forEach((panel) => {
                const active = panel.dataset.tabPanel === key;
                panel.classList.toggle('is-active', active);
                panel.hidden = !active;
            });
        });
    });

    // Multi-zip tag input
    document.querySelectorAll('[data-zip-tags]').forEach((wrap) => {
        const hidden = wrap.querySelector('input[name="zip_code"]');
        const entry = wrap.querySelector('[data-zip-entry]');
        const addBtn = wrap.querySelector('[data-zip-add]');
        const list = wrap.querySelector('.zip-tag-list');
        let tags = [];

        const renderTags = () => {
            if (!list) return;
            list.innerHTML = '';
            tags.forEach((zip) => {
                const pill = document.createElement('span');
                pill.className = 'zip-tag-pill';
                pill.innerHTML = `${zip}<button type="button" aria-label="Remove ${zip}">×</button>`;
                pill.querySelector('button')?.addEventListener('click', () => {
                    tags = tags.filter((t) => t !== zip);
                    syncValue();
                });
                list.appendChild(pill);
            });
        };

        const syncValue = () => {
            if (hidden) {
                hidden.value = tags.join(',');
            }
            renderTags();
        };

        const addZip = (value) => {
            const zip = (value || '').trim();
            if (!zip) return;
            if (!tags.includes(zip)) {
                tags.push(zip);
                syncValue();
            }
            if (entry) entry.value = '';
        };

        entry?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                addZip(entry.value);
            }
        });

        addBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            addZip(entry?.value || '');
        });

        wrap.closest('form')?.addEventListener('submit', () => {
            addZip(entry?.value || '');
        });
    });
document.querySelectorAll('[data-multi-step]').forEach((form) => {
        const steps = Array.from(form.querySelectorAll('.form-step'));
        const progressBar = form.querySelector('.form-progress-bar > div');
        const stepCurrent = form.querySelector('.step-current');
        const stepPct = form.querySelector('.step-pct');
        let currentStep = Math.max(steps.findIndex((step) => step.classList.contains('is-active')), 0);

        const updateProgress = () => {
            if (!steps.length) {
                return;
            }

            const progress = ((currentStep + 1) / steps.length) * 100;

            if (progressBar) {
                progressBar.style.width = progress + '%';
            }

            if (stepCurrent) {
                stepCurrent.textContent = String(currentStep + 1);
            }

            if (stepPct) {
                stepPct.textContent = Math.round(progress) + '%';
            }
        };

        const showStep = (index) => {
            currentStep = Math.min(Math.max(index, 0), steps.length - 1);
            steps.forEach((step, stepIndex) => {
                const active = stepIndex === currentStep;
                step.classList.toggle('is-active', active);
                step.hidden = !active;
            });
            updateProgress();
        };

        const validateStep = () => {
            const activeStep = steps[currentStep];
            const fields = Array.from(activeStep.querySelectorAll('input, select, textarea'));

            return fields.every((field) => {
                if (field.disabled || field.type === 'hidden' || !field.willValidate) {
                    return true;
                }
                return field.reportValidity();
            });
        };

        form.querySelectorAll('[data-form-next]').forEach((button) => {
            button.addEventListener('click', () => {
                if (validateStep()) {
                    showStep(currentStep + 1);
                }
            });
        });

        form.querySelectorAll('[data-form-prev]').forEach((button) => {
            button.addEventListener('click', () => showStep(currentStep - 1));
        });

        form.addEventListener('submit', (event) => {
            if (!validateStep()) {
                event.preventDefault();
            }
        });
        form.addEventListener('reset', () => showStep(0));
        showStep(currentStep);
    });

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

    document.querySelectorAll('.filter-bar, .map-card, [data-animate], [data-animate=\"left\"], [data-animate=\"right\"], [data-animate=\"up\"], [data-reveal], [data-stagger]').forEach((element) => {
        const dir = element.getAttribute('data-animate');
        if (dir === 'left' && !element.hasAttribute('data-reveal')) element.setAttribute('data-reveal', 'left');
        else if (dir === 'right' && !element.hasAttribute('data-reveal')) element.setAttribute('data-reveal', 'right');
        else if (dir && !element.hasAttribute('data-reveal')) element.setAttribute('data-reveal', '');
        revealObserver.observe(element);
    });

    document.querySelectorAll('[data-stagger]').forEach((parent) => {
        Array.from(parent.children).forEach((child, i) => {
            if (!child.hasAttribute('data-reveal')) {
                child.setAttribute('data-reveal', '');
            }
            child.setAttribute('data-reveal-delay', Math.min(i * 100, 600).toString());
            revealObserver.observe(child);
        });
    });

    document.querySelectorAll('[data-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('.testimonial-track');
        const cards = Array.from(carousel.querySelectorAll('.testimonial-card'));
        const prev = carousel.querySelector('[data-carousel-prev]');
        const next = carousel.querySelector('[data-carousel-next]');

        if (!track || !cards.length) {
            return;
        }

        let index = 0;
        let timer;

        const perView = () => (window.innerWidth <= 768 ? 1 : window.innerWidth <= 1024 ? 2 : 3);

        const update = () => {
            const maxIndex = Math.max(cards.length - perView(), 0);
            index = Math.min(index, maxIndex);
            const step = cards[0].getBoundingClientRect().width + 19.2;
            track.style.transform = `translateX(-${index * step}px)`;
        };

        const goNext = () => {
            const maxIndex = Math.max(cards.length - perView(), 0);
            index = index >= maxIndex ? 0 : index + 1;
            update();
        };

        const goPrev = () => {
            const maxIndex = Math.max(cards.length - perView(), 0);
            index = index <= 0 ? maxIndex : index - 1;
            update();
        };

        const restart = () => {
            clearInterval(timer);
            timer = setInterval(goNext, 5000);
        };

        next?.addEventListener('click', () => {
            goNext();
            restart();
        });

        prev?.addEventListener('click', () => {
            goPrev();
            restart();
        });

        carousel.addEventListener('mouseenter', () => clearInterval(timer));
        carousel.addEventListener('mouseleave', restart);
        window.addEventListener('resize', update);
        update();
        restart();
    });

    if (document.body.classList.contains('page-dashboard')) {
        document.body.classList.add('dashboard-loading');
        const dropDashboardSkeleton = () => document.body.classList.remove('dashboard-loading');
        window.addEventListener('load', dropDashboardSkeleton);
        setTimeout(dropDashboardSkeleton, 1200);
    }

    const pricingToggle = document.getElementById('pricingToggle');
    const pricingSaveBadge = document.getElementById('pricingSaveBadge');
    const oneTimeLabel = document.getElementById('toggle-onetime-label');
    const monthlyLabel = document.getElementById('toggle-monthly-label');

    if (pricingToggle) {
        const updatePricingToggle = () => {
            const monthly = pricingToggle.classList.toggle('is-monthly');
            pricingToggle.setAttribute('aria-checked', monthly ? 'true' : 'false');
            oneTimeLabel?.classList.toggle('is-active', !monthly);
            monthlyLabel?.classList.toggle('is-active', monthly);
            pricingSaveBadge?.classList.toggle('is-visible', monthly);

            document.querySelectorAll('[id^="price-onetime-"]').forEach((row) => {
                row.style.display = monthly ? 'none' : 'flex';
            });
            document.querySelectorAll('[id^="price-monthly-"]').forEach((row) => {
                row.style.display = monthly ? 'flex' : 'none';
            });
        };

        pricingToggle.addEventListener('click', updatePricingToggle);
        pricingToggle.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                updatePricingToggle();
            }
        });
    }

    const pricingCategoryButtons = Array.from(document.querySelectorAll('[data-pricing-category]'));
    const pricingPanels = Array.from(document.querySelectorAll('[data-pricing-panel]'));
    const packageSearch = document.getElementById('packageSearch');
    const packageFilterButtons = Array.from(document.querySelectorAll('[data-package-filter]'));
    const packageResultsSummary = document.getElementById('packageResultsSummary');
    let activePricingCategory = pricingCategoryButtons.find((button) => button.classList.contains('is-active'))?.dataset.pricingCategory || 'leads';
    let activePackageFilter = 'all';

    const getActivePricingPanel = () => pricingPanels.find((panel) => panel.dataset.pricingPanel === activePricingCategory);

    const applyPackageFilters = () => {
        const activePanel = getActivePricingPanel();
        if (!activePanel) {
            return;
        }

        const query = (packageSearch?.value || '').trim().toLowerCase();
        const cards = Array.from(activePanel.querySelectorAll('[data-package-card]'));
        const emptyState = activePanel.querySelector('[data-package-empty]');
        let visibleCount = 0;

        cards.forEach((card) => {
            const haystack = `${card.dataset.packageName || ''} ${card.dataset.packageFeatures || ''}`;
            const matchesSearch = !query || haystack.includes(query);
            const matchesFilter = activePackageFilter === 'all'
                || (activePackageFilter === 'featured' && card.dataset.packageFeatured === 'true')
                || (activePackageFilter === 'support' && card.dataset.packageSupport === 'true');
            const visible = matchesSearch && matchesFilter;

            card.hidden = !visible;
            card.style.display = visible ? 'flex' : 'none';
            if (visible) {
                visibleCount += 1;
            }
        });

        pricingPanels
            .filter((panel) => panel !== activePanel)
            .forEach((panel) => {
                panel.querySelectorAll('[data-package-card]').forEach((card) => {
                    card.hidden = false;
                    card.style.display = 'flex';
                });
                panel.querySelector('[data-package-empty]')?.setAttribute('hidden', 'hidden');
            });

        if (emptyState) {
            emptyState.hidden = visibleCount !== 0;
        }

        if (packageResultsSummary) {
            packageResultsSummary.textContent = `${visibleCount} package${visibleCount === 1 ? '' : 's'} match your current filters.`;
        }
    };

    pricingCategoryButtons.forEach((button) => {
        button.addEventListener('click', () => {
            activePricingCategory = button.dataset.pricingCategory || 'leads';
            pricingCategoryButtons.forEach((item) => {
                const active = item === button;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            pricingPanels.forEach((panel) => {
                const active = panel.dataset.pricingPanel === activePricingCategory;
                panel.hidden = !active;
                panel.style.display = active ? '' : 'none';
            });
            applyPackageFilters();
        });
    });

    packageFilterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            activePackageFilter = button.dataset.packageFilter || 'all';
            packageFilterButtons.forEach((item) => item.classList.toggle('is-active', item === button));
            applyPackageFilters();
        });
    });

    packageSearch?.addEventListener('input', applyPackageFilters);
    if (pricingPanels.length) {
        applyPackageFilters();
    }

    const vaAddonToggle = document.getElementById('vaAddonToggle');
    const vaAddonRail = document.getElementById('vaAddonRail');

    if (vaAddonToggle && vaAddonRail) {
        const syncVaAddonRail = () => {
            const visible = vaAddonToggle.checked && activePricingCategory === 'leads';
            vaAddonRail.hidden = !visible;
            vaAddonRail.classList.toggle('is-visible', visible);
        };

        vaAddonToggle.addEventListener('change', syncVaAddonRail);
        pricingCategoryButtons.forEach((button) => {
            button.addEventListener('click', syncVaAddonRail);
        });
        syncVaAddonRail();
    }

    const packageModal = document.getElementById('packageModal');
    const packageModalFrame = document.getElementById('packageModalFrame');
    const packageModalTitle = document.getElementById('packageModalTitle');
    const packageModalDescription = document.getElementById('packageModalDescription');
    const packageModalOnboarding = document.getElementById('packageModalOnboarding');
    const packageModalStripeCheckout = document.getElementById('packageModalStripeCheckout');
    const packageModalStatus = document.getElementById('packageModalStatus');
    const packageModalClose = document.getElementById('packageModalClose');
    const packageModalCancel = document.getElementById('packageModalCancel');

    if (packageModal && packageModalFrame && packageModalTitle && packageModalDescription && packageModalOnboarding) {
        const defaultOnboardingHref = packageModalOnboarding.getAttribute('href') || '';
        const defaultCheckoutHref = packageModalStripeCheckout?.getAttribute('href') || '';
        const defaultStatus = packageModalStatus?.textContent?.trim() || 'Complete the package form and payment to unlock onboarding.';
        let packageSubmissionComplete = false;

        const setOnboardingState = (completed) => {
            packageSubmissionComplete = completed;
            packageModalOnboarding.hidden = !completed;
            packageModalOnboarding.setAttribute('aria-hidden', completed ? 'false' : 'true');
            packageModalOnboarding.setAttribute('aria-disabled', completed ? 'false' : 'true');
            packageModalOnboarding.tabIndex = completed ? 0 : -1;

            if (packageModalStatus) {
                packageModalStatus.textContent = completed
                    ? 'Payment confirmed. You can continue to onboarding now.'
                    : defaultStatus;
            }
        };

        const resetPackageModal = () => {
            packageModal.hidden = true;
            packageModal.setAttribute('aria-hidden', 'true');
            packageModalFrame.src = 'about:blank';
            packageModalOnboarding.href = defaultOnboardingHref;
            if (packageModalStripeCheckout) {
                packageModalStripeCheckout.href = defaultCheckoutHref;
            }
            document.body.classList.remove('modal-open');
            setOnboardingState(false);
        };

        const openModal = (trigger) => {
            packageModalTitle.textContent = trigger.dataset.packageTitle || 'Complete your package selection';
            packageModalDescription.textContent = trigger.dataset.packageDescription || 'Finish the package form to move into onboarding.';
            packageModalOnboarding.href = trigger.dataset.packageOnboarding || defaultOnboardingHref;
            if (packageModalStripeCheckout) {
                packageModalStripeCheckout.href = trigger.dataset.packageCheckout || defaultCheckoutHref;
            }
            setOnboardingState(false);
            packageModalFrame.src = trigger.dataset.packageSrc || 'about:blank';
            packageModal.hidden = false;
            packageModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
        };

        const closeModal = () => resetPackageModal();

        resetPackageModal();
        window.addEventListener('pageshow', resetPackageModal);

        packageModalOnboarding.addEventListener('click', (event) => {
            if (!packageSubmissionComplete) {
                event.preventDefault();
            }
        });

        document.querySelectorAll('[data-package-modal-open]').forEach((trigger) => {
            trigger.addEventListener('click', (event) => {
                if (trigger.dataset.packageSrc) {
                    event.preventDefault();
                    openModal(trigger);
                }
            });
        });

        packageModalClose?.addEventListener('click', closeModal);
        packageModalCancel?.addEventListener('click', closeModal);
        packageModal.addEventListener('click', (event) => {
            if (event.target === packageModal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !packageModal.hidden) {
                closeModal();
            }
        });

        packageModal._setOnboardingState = setOnboardingState;
    }

    const submissionLooksComplete = (payload) => {
        if (!payload) {
            return false;
        }

        const serialized = typeof payload === 'string' ? payload.toLowerCase() : JSON.stringify(payload).toLowerCase();

        return [
            'submitted',
            'form_submitted',
            'formsubmitted',
            'survey_submitted',
            'surveysubmitted',
            'thank_you',
            'thankyou',
            'success',
            'completed',
        ].some((keyword) => serialized.includes(keyword));
    };

    const initHomeMap = () => {
        if (!document.body.classList.contains('page-home')) {
            return;
        }

        const mapContainer = document.getElementById('hero-map');
        if (!mapContainer) {
            return;
        }

        if (!window.google?.maps) {
            if (window.initHomeMapLoadingStarted) {
                return;
            }

            window.initHomeMapLoadingStarted = true;

            const apiKey = document.body.dataset.googleMapsApiKey || '';
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&libraries=places&callback=initHomeMap`;
            script.defer = true;
            script.async = true;
            document.body.appendChild(script);
            return;
        }

        const defaultLocation = { lat: 39.8283, lng: -98.5795 };

        const map = new window.google.maps.Map(mapContainer, {
            center: defaultLocation,
            zoom: 4,
            disableDefaultUI: true,
            styles: [
                { featureType: 'poi', stylers: [{ visibility: 'off' }] },
                { featureType: 'transit', stylers: [{ visibility: 'off' }] },
                { featureType: 'water', stylers: [{ color: '#a8d4f7' }] },
            ],
        });

        const marker = new window.google.maps.Marker({
            map,
            position: defaultLocation,
            icon: {
                path: 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z',
                fillColor: '#FF6B00',
                fillOpacity: 1,
                strokeColor: '#fff',
                strokeWeight: 2,
                scale: 1.8,
                anchor: new window.google.maps.Point(12, 24),
            },
        });

        const geocoder = new window.google.maps.Geocoder();

        const updateZipInfo = (zip) => {
            const zipDisplay = document.getElementById('buyer-zip-display');
            const zipStatus = document.getElementById('buyer-zip-status');
            if (zipDisplay) zipDisplay.textContent = zip || 'Enter ZIP';
            if (zipStatus) zipStatus.textContent = zip ? 'Searching...' : 'Awaiting input';

            if (!zip || zip.length < 5) {
                if (zipStatus) zipStatus.textContent = 'Enter a 5-digit ZIP code';
                return;
            }

            geocoder.geocode({ address: zip }, (results, status) => {
                if (status === 'OK' && results?.[0]) {
                    const position = results[0].geometry.location;
                    marker.setPosition(position);
                    map.panTo(position);
                    map.setZoom(11);

                    if (zipStatus) {
                        zipStatus.textContent = results[0].formatted_address;
                    }
                } else {
                    if (zipStatus) {
                        zipStatus.textContent = 'Unable to find this ZIP code';
                    }
                }
            });
        };

        const zipInputSelectors = ['input[name="zip_code"]'];
        zipInputSelectors.forEach((selector) => {
            document.querySelectorAll(selector).forEach((input) => {
                input.addEventListener('input', (event) => {
                    const value = String(event.target.value || '').trim();
                    const sanitized = value.replace(/[^0-9]/g, '');
                    if (event.target.value !== sanitized) {
                        event.target.value = sanitized;
                    }
                    updateZipInfo(sanitized);
                });
            });
        });

        setTimeout(() => mapContainer.classList.add('is-loaded'), 250);
    };

    const statItems = document.querySelectorAll('.stat-strip__item[data-counter]');
    if (statItems.length) {
        const countUp = (el) => {
            const target = parseInt(el.dataset.counter, 10);
            const suffix = el.dataset.suffix || '';
            const numberEl = el.querySelector('.stat-strip__number');
            if (!numberEl || Number.isNaN(target)) return;
            const duration = 1800;
            const start = performance.now();
            const animate = (now) => {
                const elapsed = now - start;
                const progress = Math.min(elapsed / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = Math.round(eased * target);
                numberEl.textContent = current.toLocaleString() + suffix;
                if (progress < 1) {
                    requestAnimationFrame(animate);
                }
            };
            requestAnimationFrame(animate);
        };


        const statObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting && !entry.target.dataset.counted) {
                    entry.target.dataset.counted = 'true';
                    countUp(entry.target);
                }
            });
        }, { threshold: 0.5 });

        statItems.forEach((item) => statObserver.observe(item));
    }

    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        const showError = (fieldId, errorId, show) => {
            const field = document.getElementById(fieldId);
            const error = document.getElementById(errorId);
            if (!field || !error) return;
            error.style.display = show ? 'block' : 'none';
            field.style.borderColor = show ? '#dc2626' : '';
        };

        const validateEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

        contactForm.addEventListener('submit', (event) => {
            let valid = true;
            const name = document.getElementById('contactName');
            const email = document.getElementById('contactEmail');
            const message = document.getElementById('contactMessage');

            if (!name || !name.value.trim()) { showError('contactName', 'nameError', true); valid = false; } else { showError('contactName', 'nameError', false); }
            if (!email || !validateEmail(email.value)) { showError('contactEmail', 'emailError', true); valid = false; } else { showError('contactEmail', 'emailError', false); }
            if (!message || !message.value.trim()) { showError('contactMessage', 'messageError', true); valid = false; } else { showError('contactMessage', 'messageError', false); }

            if (!valid) {
                event.preventDefault();
                contactForm.querySelector('.field-error[style*="block"]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            const successBox = document.getElementById('contactSuccess');
            if (successBox) {
                event.preventDefault();
                contactForm.style.display = 'none';
                successBox.style.display = 'block';
                successBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                fetch(contactForm.action, { method: 'POST', body: new FormData(contactForm) });
            }
        });

        ['contactName', 'contactEmail', 'contactMessage'].forEach((id) => {
            const el = document.getElementById(id);
            el?.addEventListener('input', () => {
                const errorMap = { contactName: 'nameError', contactEmail: 'emailError', contactMessage: 'messageError' };
                showError(id, errorMap[id], false);
            });
        });
    }

    const dashboardSwitch = document.querySelector('[data-dashboard-switch]');
    const onboardingCard = document.querySelector('[data-onboarding-embed]');
    const onboardingContinueButton = document.getElementById('onboardingContinueButton');

    if (dashboardSwitch && onboardingCard) {
        dashboardSwitch.querySelectorAll('[data-dashboard-target]').forEach((button) => {
            button.addEventListener('click', () => {
                dashboardSwitch.querySelectorAll('[data-dashboard-target]').forEach((item) => item.classList.remove('is-active'));
                button.classList.add('is-active');
                onboardingCard.dataset.dashboardUrl = button.dataset.dashboardTarget || onboardingCard.dataset.dashboardUrl;
                if (onboardingContinueButton) {
                    onboardingContinueButton.href = onboardingCard.dataset.dashboardUrl;
                }
            });
        });
    }

    window.addEventListener('message', (event) => {
        const origin = event.origin || '';
        const trusted = origin.includes('leadconnectorhq.com') || origin.includes('msgsndr.com');

        if (!trusted || !submissionLooksComplete(event.data)) {
            return;
        }

        const activeOnboardingCard = document.querySelector('[data-onboarding-embed]');
        const dashboardUrl = activeOnboardingCard?.dataset.dashboardUrl;

        if (activeOnboardingCard && dashboardUrl) {
            window.location.href = dashboardUrl;
            return;
        }

        if (packageModal && !packageModal.hidden && typeof packageModal._setOnboardingState === 'function') {
            packageModal._setOnboardingState(true);
        }
    });

    const listingsForm = document.getElementById('listingsFilterForm');
    if (listingsForm) {
        const zipInput = document.getElementById('filterZip');
        const typeInput = document.getElementById('filterType');
        const priceInput = document.getElementById('filterPrice');
        const applyBtn = document.getElementById('filterApply');
        const resetBtn = document.getElementById('filterReset');
        const saveBtn = document.getElementById('filterSaveSearch');
        const sortInput = document.getElementById('listingSort');
        const resultsCount = document.getElementById('listingResultsCount');
        const emptyState = document.getElementById('listingEmptyState');
        const mapFilterChip = document.getElementById('mapFilterChip');
        const cards = Array.from(document.querySelectorAll('[data-listing-card]'));
        const defaultMapSrc = mapFrame?.getAttribute('src') || '';
        const storageKey = 'omnireferral:listings-filters';

        const renderCards = () => {
            const zipValue = (zipInput?.value || '').trim().toLowerCase();
            const typeValue = (typeInput?.value || '').trim().toLowerCase();
            const [minPrice, maxPrice] = parsePriceRange(priceInput?.value || '');
            const sortValue = sortInput?.value || 'relevant';

            const filteredCards = cards.filter((card) => {
                const cardZip = (card.dataset.zip || '').toLowerCase();
                const cardType = (card.dataset.type || '').toLowerCase();
                const cardPrice = parseInt(card.dataset.price || '0', 10);
                const zipMatch = !zipValue || cardZip.includes(zipValue);
                const typeMatch = !typeValue || cardType === typeValue;
                const priceMatch = cardPrice >= minPrice && cardPrice <= maxPrice;

                return zipMatch && typeMatch && priceMatch;
            });

            filteredCards.sort((a, b) => {
                const aPrice = parseInt(a.dataset.price || '0', 10);
                const bPrice = parseInt(b.dataset.price || '0', 10);
                const aType = a.dataset.type || '';
                const bType = b.dataset.type || '';
                const aOrder = parseInt(a.dataset.order || '0', 10);
                const bOrder = parseInt(b.dataset.order || '0', 10);

                if (sortValue === 'price-asc') return aPrice - bPrice;
                if (sortValue === 'price-desc') return bPrice - aPrice;
                if (sortValue === 'type') return aType.localeCompare(bType);
                return aOrder - bOrder;
            });

            cards.forEach((card) => {
                card.hidden = true;
                card.style.display = 'none';
            });

            filteredCards.forEach((card) => {
                card.hidden = false;
                card.style.display = 'flex';
                listingGrid?.appendChild(card);
            });

            if (resultsCount) {
                resultsCount.textContent = String(filteredCards.length);
            }
            if (emptyState) {
                emptyState.hidden = filteredCards.length !== 0;
            }
            if (mapFrame) {
                const newSrc = zipValue
                    ? `https://www.google.com/maps?q=${encodeURIComponent(zipValue)}&output=embed`
                    : defaultMapSrc;
                mapFrame.src = newSrc;
                if (listingsMapPanelFrame) {
                    listingsMapPanelFrame.src = newSrc;
                }
            }
            if (mapFilterChip) {
                mapFilterChip.textContent = zipValue ? `ZIP ${zipValue.toUpperCase()}` : 'Map Ready';
            }
        };

        const saveCurrentFilters = () => {
            const payload = {
                zip: zipInput?.value || '',
                type: typeInput?.value || '',
                price: priceInput?.value || '',
                sort: sortInput?.value || 'relevant',
            };
            localStorage.setItem(storageKey, JSON.stringify(payload));
            if (saveBtn) {
                const previousLabel = saveBtn.textContent;
                saveBtn.textContent = 'Search Saved';
                window.setTimeout(() => {
                    saveBtn.textContent = previousLabel;
                }, 1400);
            }
        };

        try {
            const savedFilters = JSON.parse(localStorage.getItem(storageKey) || 'null');
            if (savedFilters) {
                if (zipInput) zipInput.value = savedFilters.zip || '';
                if (typeInput) typeInput.value = savedFilters.type || '';
                if (priceInput) priceInput.value = savedFilters.price || '';
                if (sortInput) sortInput.value = savedFilters.sort || 'relevant';
            }
        } catch (error) {
            localStorage.removeItem(storageKey);
        }

        ['input', 'change'].forEach((eventName) => {
            zipInput?.addEventListener(eventName, renderCards);
            typeInput?.addEventListener(eventName, renderCards);
            priceInput?.addEventListener(eventName, renderCards);
            sortInput?.addEventListener(eventName, renderCards);
        });
        applyBtn?.addEventListener('click', renderCards);
        resetBtn?.addEventListener('click', () => {
            listingsForm.reset();
            if (sortInput) {
                sortInput.value = 'relevant';
            }
            localStorage.removeItem(storageKey);
            renderCards();
        });
        saveBtn?.addEventListener('click', saveCurrentFilters);
        renderCards();
    }

    const listingsMapPanelFrame = document.getElementById('listingsMapPanelFrame');
    const listingGrid = document.getElementById('listingGrid');
    const mapFrame = document.getElementById('listingsMapFrame');
    const listingsViewButtons = Array.from(document.querySelectorAll('[data-view-toggle]'));
    const listingsMapPanel = document.getElementById('listingsMapPanel');
    const listingsMainWrapper = document.querySelector('.listings-main');

    const setListingsViewMode = (mode) => {
        const showMap = mode === 'map';
        if (listingGrid) {
            listingGrid.hidden = showMap;
            listingGrid.style.display = showMap ? 'none' : '';
        }
        if (listingsMapPanel) {
            listingsMapPanel.hidden = !showMap;
        }
        listingsViewButtons.forEach((button) => {
            const active = button.dataset.viewToggle === mode;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', String(active));
        });
        if (listingsMainWrapper) {
            listingsMainWrapper.classList.toggle('listings-main--map-view', showMap);
        }
        if (listingsMapPanelFrame && mapFrame) {
            listingsMapPanelFrame.src = mapFrame.src;
        }
    };

    listingsViewButtons.forEach((button) => {
        button.addEventListener('click', () => setListingsViewMode(button.dataset.viewToggle || 'list'));
    });

    setListingsViewMode('list');

    const agentDirectoryGrid = document.getElementById('agentDirectoryGrid');
    if (agentDirectoryGrid) {
        const agentCityFilter = document.getElementById('agentCityFilter');
        const agentSpecialtyFilter = document.getElementById('agentSpecialtyFilter');
        const agentCards = Array.from(document.querySelectorAll('[data-agent-card]'));

        const applyAgentFilters = () => {
            const cityValue = (agentCityFilter?.value || '').trim().toLowerCase();
            const specialtyValue = (agentSpecialtyFilter?.value || '').trim().toLowerCase();
            agentCards.forEach((card) => {
                const matchesCity = !cityValue || (card.dataset.city || '').toLowerCase().includes(cityValue);
                const matchesSpecialty = !specialtyValue || (card.dataset.specialty || '').toLowerCase().includes(specialtyValue);
                const visible = matchesCity && matchesSpecialty;
                card.hidden = !visible;
                card.style.display = visible ? '' : 'none';
            });
        };

        agentCityFilter?.addEventListener('change', applyAgentFilters);
        agentSpecialtyFilter?.addEventListener('change', applyAgentFilters);
        applyAgentFilters();
    }

    const authRoleTabs = Array.from(document.querySelectorAll('[data-role-tab]'));
    const authRoleSelect = document.getElementById('loginRoleSelect') || document.getElementById('registerRoleSelect');
    if (authRoleTabs.length && authRoleSelect) {
        const syncRoleTabState = (category) => {
            authRoleTabs.forEach((tab) => {
                const active = tab.dataset.roleTab === category;
                tab.classList.toggle('is-active', active);
            });
            Array.from(authRoleSelect.options).forEach((option) => {
                const optionCategory = option.dataset.roleGroup || '';
                option.hidden = optionCategory !== category;
                option.disabled = optionCategory !== category;
            });
            const selectedOption = Array.from(authRoleSelect.options).find((option) => !option.disabled);
            if (selectedOption) {
                selectedOption.selected = true;
            }
        };

        authRoleTabs.forEach((tab) => {
            tab.addEventListener('click', () => syncRoleTabState(tab.dataset.roleTab));
        });

        const initialCategory = authRoleSelect.selectedOptions[0]?.dataset.roleGroup || 'buyer';
        syncRoleTabState(initialCategory);
    }

    const buyerDashboardZipFilter = document.getElementById('buyerDashboardZipFilter');
    if (buyerDashboardZipFilter) {
        const buyerDashboardSearchButton = document.getElementById('buyerDashboardSearchButton');
        const buyerDashboardResultsSummary = document.getElementById('buyerDashboardResultsSummary');
        const buyerDashboardEmptyState = document.getElementById('buyerDashboardEmptyState');
        const buyerDashboardMapFrame = document.getElementById('buyerDashboardMapFrame');
        const buyerDashboardListingGrid = document.getElementById('buyerDashboardListingGrid');
        const buyerCards = Array.from(document.querySelectorAll('[data-buyer-listing-card]'));
        const defaultBuyerMapSrc = buyerDashboardMapFrame?.getAttribute('src') || '';

        const renderBuyerDashboardListings = () => {
            const zipValue = buyerDashboardZipFilter.value.trim().toLowerCase();
            const filtered = buyerCards.filter((card) => !zipValue || (card.dataset.zip || '').toLowerCase().includes(zipValue));

            buyerCards.forEach((card) => {
                card.hidden = true;
                card.style.display = 'none';
            });

            filtered
                .sort((a, b) => parseInt(a.dataset.order || '0', 10) - parseInt(b.dataset.order || '0', 10))
                .forEach((card) => {
                    card.hidden = false;
                    card.style.display = '';
                    buyerDashboardListingGrid?.appendChild(card);
                });

            if (buyerDashboardResultsSummary) {
                buyerDashboardResultsSummary.textContent = zipValue
                    ? `${filtered.length} saved home${filtered.length === 1 ? '' : 's'} in or near ZIP ${zipValue.toUpperCase()}.`
                    : `Showing ${filtered.length} saved home${filtered.length === 1 ? '' : 's'}.`;
            }
            if (buyerDashboardEmptyState) {
                buyerDashboardEmptyState.hidden = filtered.length !== 0;
            }
            if (buyerDashboardMapFrame) {
                buyerDashboardMapFrame.src = zipValue
                    ? `https://www.google.com/maps?q=${encodeURIComponent(zipValue)}&output=embed`
                    : defaultBuyerMapSrc;
            }
        };

        buyerDashboardZipFilter.addEventListener('input', renderBuyerDashboardListings);
        buyerDashboardSearchButton?.addEventListener('click', renderBuyerDashboardListings);
        renderBuyerDashboardListings();
    }

    const validationForms = document.querySelectorAll('.auth-form-shell, .hero-form, .contact-form');
    const updateFieldState = (field) => {
        if (!field || !field.willValidate || field.type === 'hidden' || field.type === 'file') {
            return;
        }

        const value = typeof field.value === 'string' ? field.value.trim() : field.value;
        if (!value) {
            field.classList.remove('is-valid', 'is-invalid');
            return;
        }

        const valid = field.checkValidity();
        field.classList.toggle('is-valid', valid);
        field.classList.toggle('is-invalid', !valid);
    };

    validationForms.forEach((form) => {
        form.querySelectorAll('input, select, textarea').forEach((field) => {
            field.addEventListener('blur', () => updateFieldState(field));
            field.addEventListener('input', () => updateFieldState(field));
            field.addEventListener('change', () => updateFieldState(field));
        });

        if (form.classList.contains('auth-form-shell')) {
            form.addEventListener('submit', (event) => {
                const fields = Array.from(form.querySelectorAll('input, select, textarea')).filter((field) => field.willValidate && field.type !== 'hidden' && field.type !== 'file');
                fields.forEach(updateFieldState);
                const firstInvalid = fields.find((field) => !field.checkValidity());
                if (firstInvalid) {
                    event.preventDefault();
                    firstInvalid.focus();
                }
            });
        }
    });
    document.querySelectorAll('img').forEach((img) => {
        if (!img.hasAttribute('loading')) {
            img.setAttribute('loading', 'lazy');
        }
    });
});












