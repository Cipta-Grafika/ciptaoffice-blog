export function initArticleTables(root = document) {
    root.querySelectorAll('[data-article-content]').forEach((content) => {
        content.querySelectorAll('.ql-table-wrapper').forEach((wrapper, index) => {
            const table = wrapper.querySelector('table');
            if (!table) return;

            const updateScrollableState = () => {
                const isScrollable = wrapper.scrollWidth > wrapper.clientWidth + 1;
                wrapper.classList.toggle('is-scrollable', isScrollable);

                if (isScrollable) {
                    wrapper.tabIndex = 0;
                    wrapper.setAttribute('role', 'region');
                    wrapper.setAttribute(
                        'aria-label',
                        `Tabel ${index + 1} dalam artikel. Geser horizontal untuk melihat seluruh kolom.`,
                    );
                } else {
                    wrapper.removeAttribute('tabindex');
                    wrapper.removeAttribute('role');
                    wrapper.removeAttribute('aria-label');
                }
            };

            updateScrollableState();

            if ('ResizeObserver' in window) {
                const observer = new ResizeObserver(updateScrollableState);
                observer.observe(wrapper);
                observer.observe(table);
            } else {
                window.addEventListener('resize', updateScrollableState, { passive: true });
            }
        });
    });
}
