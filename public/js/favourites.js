// favourites.js - Cookie-based guest favourites system
const COOKIE_NAME = 'guest_favourites';
const COOKIE_DAYS = 365;

function getFavourites() {
    const cookie = document.cookie
        .split('; ')
        .find(row => row.startsWith(COOKIE_NAME + '='));
    if (!cookie) return [];
    try {
        return JSON.parse(decodeURIComponent(cookie.split('=')[1]));
    } catch {
        return [];
    }
}

function saveFavourites(ids) {
    const expires = new Date();
    expires.setDate(expires.getDate() + COOKIE_DAYS);
    document.cookie = `${COOKIE_NAME}=${encodeURIComponent(JSON.stringify(ids))};` 
        + `expires=${expires.toUTCString()};path=/;SameSite=Lax`;
}

function toggleFavourite(listingId) {
    let favs = getFavourites();
    const index = favs.indexOf(listingId);
    if (index === -1) {
        favs.push(listingId);
        showToast('Added to your favourites ❤');
    } else {
        favs.splice(index, 1);
        showToast('Removed from favourites');
    }
    saveFavourites(favs);
    updateHeartIcons();
}

function updateHeartIcons() {
    const favs = getFavourites();
    document.querySelectorAll('[data-listing-id]').forEach(btn => {
        const id = parseInt(btn.dataset.listingId);
        btn.classList.toggle('is-favourite', favs.includes(id));
    });
}

function showToast(message) {
    const toast = document.getElementById('fav-toast');
    if (toast) {
        toast.textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    updateHeartIcons();
    document.querySelectorAll('[data-listing-id]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            toggleFavourite(parseInt(btn.dataset.listingId));
        });
    });
});
