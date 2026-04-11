<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleGroups = document.querySelectorAll('[data-pricing-toggle]');

    toggleGroups.forEach(function(group) {
        const groupName = group.getAttribute('data-pricing-toggle');
        const labels = group.querySelectorAll('[data-category]');
        const toggleBtn = group.querySelector('.toggle');
        const thumb = group.querySelector('.toggle-thumb');
        const grids = document.querySelectorAll(`[data-pricing-grid="${groupName}"]`);
        let current = group.dataset.default || 'real_estate';

        const setState = (category) => {
            current = category;

            labels.forEach(label => {
                const isMatch = label.getAttribute('data-category') === category;
                label.classList.toggle('is-active', isMatch);
            });

            if (thumb) {
                thumb.classList.toggle('is-active', category === 'virtual_assistance');
            }

            if (toggleBtn) {
                toggleBtn.setAttribute('aria-pressed', category === 'virtual_assistance');
            }

            grids.forEach(grid => {
                const match = grid.getAttribute('data-category') === category;
                grid.style.display = match ? '' : 'none';
            });
        };

        labels.forEach(label => label.addEventListener('click', () => setState(label.getAttribute('data-category'))));
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => setState(current === 'real_estate' ? 'virtual_assistance' : 'real_estate'));
        }

        setState(current);
    });
});
</script>
