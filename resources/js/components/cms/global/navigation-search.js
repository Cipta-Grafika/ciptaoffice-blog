export function initCmsNavigationSearch(root = document) {
    root.querySelectorAll('[data-cms-nav-search]').forEach((input) => {
        const dropdown = input.closest('.dropdown');
        if (!dropdown) return;
        const items = [...dropdown.querySelectorAll('[data-cms-nav-search-item]')];
        const empty = dropdown.querySelector('[data-cms-nav-search-empty]');

        const filterItems = () => {
            const query = input.value.trim().toLocaleLowerCase('id');
            let visibleItems = 0;
            items.forEach((item) => {
                const visible = item.textContent.trim().toLocaleLowerCase('id').includes(query);
                item.classList.toggle('d-none', !visible);
                if (visible) visibleItems += 1;
            });
            empty?.classList.toggle('d-none', visibleItems > 0);
        };

        input.addEventListener('input', filterItems);
        dropdown.addEventListener('shown.bs.dropdown', () => input.focus());
        dropdown.addEventListener('hidden.bs.dropdown', () => {
            input.value = '';
            filterItems();
        });
    });
}
