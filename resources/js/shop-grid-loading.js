/**
 * SillaGallery shop grid loading.
 *
 * Amun-style staggered reveal, adapted from the visual approach in:
 * https://github.com/codrops/GridLoadingAnimations
 *
 * Uses the browser Web Animations API; no demo images, jQuery, Masonry,
 * extra animation package, or global CSS reset is required.
 */
function initializeShopGridLoading() {
    const grids = document.querySelectorAll('[data-shop-grid]');

    if (!grids.length) return;

    const prefersReducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion || typeof Element.prototype.animate !== 'function') {
        return;
    }

    grids.forEach((grid) => {
        // Match the reference demo's visual left-to-right sequence.
        const items = Array.from(grid.querySelectorAll('[data-shop-grid-item]')).sort((first, second) => {
            const firstBounds = first.getBoundingClientRect();
            const secondBounds = second.getBoundingClientRect();

            return (firstBounds.left - secondBounds.left) || (firstBounds.top - secondBounds.top);
        });

        items.forEach((item, index) => {
            const delay = index * 20;

            const movement = item.animate(
                [
                    { transform: 'translate3d(0, 400px, 0)' },
                    { transform: 'translate3d(0, 0, 0)' },
                ],
                {
                    duration: 500 + (index * 50),
                    delay,
                    easing: 'cubic-bezier(0.19, 1, 0.22, 1)',
                    fill: 'both',
                },
            );

            const fade = item.animate(
                [
                    { opacity: 0 },
                    { opacity: 1 },
                ],
                {
                    duration: 250 + (index * 50),
                    delay,
                    easing: 'linear',
                    fill: 'both',
                },
            );

            // Release finished animations so the browser can discard them.
            movement.onfinish = () => movement.cancel();
            fade.onfinish = () => fade.cancel();
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeShopGridLoading, { once: true });
} else {
    requestAnimationFrame(() => requestAnimationFrame(initializeShopGridLoading));
}
