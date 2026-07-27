export function initCmsSidebar(root = document) {
    const cmsSidebarCollapse = root.querySelector('[data-cms-sidebar-collapse]');
    if (!cmsSidebarCollapse) return;

    const cmsSidebarBrand = root.querySelector('[data-cms-sidebar-brand]');
    const breakpoint = window.matchMedia('(min-width: 768px)');
    const storageKey = 'cms-sidebar-collapsed';
    const icon = cmsSidebarCollapse.querySelector('i');

    const storedPreference = () => {
        try {
            return localStorage.getItem(storageKey) === 'true';
        } catch (error) {
            return false;
        }
    };

    const applySidebarState = (collapsed) => {
        const isCollapsed = collapsed && breakpoint.matches;
        document.documentElement.classList.toggle('cms-sidebar-collapsed', isCollapsed);
        cmsSidebarCollapse.setAttribute('aria-expanded', String(!isCollapsed));
        cmsSidebarCollapse.setAttribute('aria-label', isCollapsed ? 'Perluas sidebar' : 'Perkecil sidebar');
        if (isCollapsed) {
            cmsSidebarBrand?.setAttribute('aria-label', 'Perluas sidebar');
            cmsSidebarBrand?.setAttribute('aria-expanded', 'false');
            cmsSidebarBrand?.setAttribute('title', 'Perluas sidebar');
        } else {
            cmsSidebarBrand?.removeAttribute('aria-label');
            cmsSidebarBrand?.removeAttribute('aria-expanded');
            cmsSidebarBrand?.removeAttribute('title');
        }
        icon?.classList.toggle('bi-chevron-left', !isCollapsed);
        icon?.classList.toggle('bi-chevron-right', isCollapsed);
    };

    applySidebarState(storedPreference());
    cmsSidebarCollapse.addEventListener('click', () => {
        const collapsed = !document.documentElement.classList.contains('cms-sidebar-collapsed');
        try {
            localStorage.setItem(storageKey, String(collapsed));
        } catch (error) {}
        applySidebarState(collapsed);
    });
    cmsSidebarBrand?.addEventListener('click', (event) => {
        if (!breakpoint.matches || !document.documentElement.classList.contains('cms-sidebar-collapsed')) return;
        event.preventDefault();
        try {
            localStorage.setItem(storageKey, 'false');
        } catch (error) {}
        applySidebarState(false);
        cmsSidebarBrand.focus({ preventScroll: true });
    });
    breakpoint.addEventListener('change', () => applySidebarState(storedPreference()));
}
