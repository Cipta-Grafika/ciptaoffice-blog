export function initSiteHeader(root = document) {
    const nav = root.querySelector('.site-nav');
    const siteHeader = root.querySelector('[data-site-header]');

    if (!siteHeader) return;

    const syncSiteHeaderHeight = () => {
        document.documentElement.style.setProperty('--site-nav-height', `${siteHeader.offsetHeight}px`);
    };

    syncSiteHeaderHeight();
    window.addEventListener('resize', syncSiteHeaderHeight, { passive: true });
    if ('ResizeObserver' in window) new ResizeObserver(syncSiteHeaderHeight).observe(siteHeader);

    if (nav) {
        window.addEventListener(
            'scroll',
            () => nav.classList.toggle('scrolled', window.scrollY > 12),
            { passive: true },
        );
    }
}
