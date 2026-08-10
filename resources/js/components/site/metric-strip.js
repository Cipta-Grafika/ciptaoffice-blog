export function initMetricStrip(root = document) {
    const nav = root.querySelector('.site-nav');
    const siteHeader = root.querySelector('[data-site-header]');
    const metricStrip = root.querySelector('[data-metric-strip]');
    const metricStripSentinel = root.querySelector('[data-metric-strip-sentinel]');

    if (!nav || !metricStrip || !metricStripSentinel) return;

    const documentRoot = document.documentElement;
    const links = [...metricStrip.querySelectorAll('.metric-section-link')];
    const sections = links.map((link) => root.querySelector(link.hash));
    let dockThreshold = 0;
    let ticking = false;

    const setDocked = (docked) => {
        if (metricStrip.classList.contains('is-docked') === docked) return;

        metricStrip.classList.toggle('is-docked', docked);
        nav.classList.toggle('has-section-nav', docked);
        links.forEach((link) => {
            link.toggleAttribute('aria-hidden', !docked);
            link.tabIndex = docked ? 0 : -1;
        });
        requestAnimationFrame(() => {
            documentRoot.style.setProperty('--metric-nav-height', `${metricStrip.offsetHeight}px`);
        });
    };

    const updateScrollState = () => {
        const navHeight = siteHeader?.offsetHeight ?? nav.offsetHeight;
        const docked = window.scrollY >= dockThreshold;
        setDocked(docked);

        const activationLine = navHeight + (docked ? metricStrip.offsetHeight : 0) + window.innerHeight * 0.25;
        let activeSection = null;
        sections.forEach((section) => {
            if (section && section.getBoundingClientRect().top <= activationLine) activeSection = section.id;
        });
        links.forEach((link) => {
            const active = docked && link.hash === `#${activeSection}`;
            link.classList.toggle('active', active);
            if (active) link.setAttribute('aria-current', 'location');
            else link.removeAttribute('aria-current');
        });
    };

    links.forEach((link) => {
        link.addEventListener('click', (e) => {
            const target = root.querySelector(link.hash);
            if (!target) return;
            e.preventDefault();
            
            const navHeight = siteHeader?.offsetHeight ?? nav.offsetHeight;
            const dockedHeight = 52; // 3.25rem * 16px (approx docked height)
            const isDocked = metricStrip.classList.contains('is-docked');
            const shrinkage = isDocked ? 0 : (metricStrip.offsetHeight - dockedHeight);
            
            // Calculate absolute target position minus nav height, docked height, and layout shrinkage
            const targetY = target.getBoundingClientRect().top + window.scrollY - navHeight - dockedHeight - shrinkage;
            
            window.scrollTo({ top: targetY, behavior: 'smooth' });
            
            // Focus the target for accessibility
            target.setAttribute('tabindex', '-1');
            target.focus({ preventScroll: true });
        });
    });

    const queueScrollUpdate = () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => {
            updateScrollState();
            ticking = false;
        });
    };

    const measureStickyPosition = () => {
        const navHeight = siteHeader?.offsetHeight ?? nav.offsetHeight;
        documentRoot.style.setProperty('--site-nav-height', `${navHeight}px`);
        dockThreshold = metricStripSentinel.getBoundingClientRect().top + window.scrollY - navHeight;
        documentRoot.style.setProperty('--metric-nav-height', `${metricStrip.offsetHeight}px`);
        updateScrollState();
    };

    measureStickyPosition();
    window.addEventListener('scroll', queueScrollUpdate, { passive: true });
    window.addEventListener('resize', measureStickyPosition, { passive: true });
    if ('ResizeObserver' in window) new ResizeObserver(measureStickyPosition).observe(siteHeader ?? nav);
}
