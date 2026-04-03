import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// --- Configuration & Helpers ---
const HEADER_OFFSET = 24;
const SCROLL_THRESHOLD = 24;

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
const siteHeader = document.getElementById('siteHeader');
const menuToggle = document.getElementById('menuToggle');
const mainNav = document.getElementById('mainNav');

// --- Header & Navigation Logic ---
if (siteHeader) {
    let lastScrollY = window.scrollY;
    let ticking = false;

    const updateHeader = () => {
        const scrollY = window.scrollY;
        siteHeader.classList.toggle('is-scrolled', scrollY > SCROLL_THRESHOLD);
        lastScrollY = scrollY;
        ticking = false;
    };

    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(updateHeader);
            ticking = true;
        }
    }, { passive: true });

    updateHeader();
}

if (menuToggle && mainNav) {
    const toggleMenu = (open) => {
        const isOpen = typeof open === 'boolean' ? open : !mainNav.classList.contains('is-open');
        mainNav.classList.toggle('is-open', isOpen);
        menuToggle.classList.toggle('is-open', isOpen);
        menuToggle.setAttribute('aria-expanded', isOpen);
        document.body.style.overflow = isOpen ? 'hidden' : '';
    };

    menuToggle.addEventListener('click', toggleMenu);
    mainNav.querySelectorAll('a').forEach(link => link.addEventListener('click', () => toggleMenu(false)));
    document.addEventListener('keydown', e => e.key === 'Escape' && toggleMenu(false));
}

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
