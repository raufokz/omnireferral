<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleHandlers = new Map();
    const toggleGroups = document.querySelectorAll('[data-pricing-toggle]');

    toggleGroups.forEach(function(group) {
        const groupName = group.getAttribute('data-pricing-toggle');
        const labels = group.querySelectorAll('[data-category]');
        const helper = group.parentElement ? group.parentElement.querySelector('[data-pricing-toggle-helper]') : null;
        const toggleBtn = group.querySelector('.toggle');
        const thumb = group.querySelector('.toggle-thumb');
        const grids = document.querySelectorAll(`[data-pricing-grid="${groupName}"]`);
        let current = group.dataset.default || 'real_estate';

        const setState = (category) => {
            current = category;

            labels.forEach(label => {
                const isMatch = label.getAttribute('data-category') === category;
                label.classList.toggle('is-active', isMatch);

                if (label.hasAttribute('aria-selected')) {
                    label.setAttribute('aria-selected', isMatch ? 'true' : 'false');
                }

                if (label.matches('button')) {
                    label.tabIndex = isMatch ? 0 : -1;
                }

                if (isMatch && helper && label.dataset.helper) {
                    helper.textContent = label.dataset.helper;
                }
            });

            if (thumb) {
                thumb.classList.toggle('is-active', category === 'virtual_assistance');
            }

            if (toggleBtn) {
                toggleBtn.setAttribute('aria-pressed', category === 'virtual_assistance');
            }

            grids.forEach(grid => {
                const match = grid.getAttribute('data-category') === category;
                grid.hidden = !match;
                grid.style.display = match ? '' : 'none';
            });
        };

        labels.forEach(label => {
            label.addEventListener('click', () => setState(label.getAttribute('data-category')));
            label.addEventListener('keydown', event => {
                if (!label.matches('button')) {
                    return;
                }

                if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') {
                    return;
                }

                event.preventDefault();
                const nextCategory = current === 'real_estate' ? 'virtual_assistance' : 'real_estate';
                setState(nextCategory);
                const nextLabel = group.querySelector(`[data-category="${nextCategory}"]`);

                if (nextLabel) {
                    nextLabel.focus();
                }
            });
        });

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => setState(current === 'real_estate' ? 'virtual_assistance' : 'real_estate'));
        }

        toggleHandlers.set(groupName, setState);
        setState(current);
    });

    document.querySelectorAll('[data-pricing-shortcut]').forEach(shortcut => {
        shortcut.addEventListener('click', () => {
            const groupName = shortcut.dataset.pricingGroup || 'pricing-page';
            const category = shortcut.dataset.pricingShortcut;
            const handler = toggleHandlers.get(groupName);

            if (handler && category) {
                handler(category);
            }
        });
    });
});
</script>
