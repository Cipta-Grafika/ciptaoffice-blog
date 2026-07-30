export function initCmsAjaxTable() {
    const containers = document.querySelectorAll('[data-cms-ajax-container]');
    
    containers.forEach(container => {
        // Sembunyikan tombol filter (fallback form submit) jika JS jalan
        const hideSubmitButtons = () => {
            const submitBtns = container.querySelectorAll('.cms-filter-form button[type="submit"]');
            submitBtns.forEach(btn => btn.style.display = 'none');
        };
        hideSubmitButtons();

        const fetchAndUpdate = async (url) => {
            // Hapus page dari URL agar filter/sort mereset ke halaman pertama jika fetchAndUpdate dipanggil karena filter/sort
            // Kita akan tangani ini di dalam handler masing-masing.

            // Menambahkan state loading sederhana
            container.style.opacity = '0.5';
            container.style.pointerEvents = 'none';

            try {
                const response = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) throw new Error('Network response was not ok');
                
                const html = await response.text();
                container.innerHTML = html;
                hideSubmitButtons();
                
                window.history.pushState({}, '', url);
            } catch (error) {
                console.error('Error fetching data:', error);
                alert('Gagal mengambil data. Silakan muat ulang halaman.');
            } finally {
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
            }
        };

        // Handle Form Changes (Auto submit on Select, Date, Text change)
        container.addEventListener('change', (e) => {
            const form = e.target.closest('.cms-filter-form');
            if (form) {
                const formData = new FormData(form);
                const currentUrl = new URL(window.location.href);
                
                // Hapus key yang ada di form dari URL saat ini
                for (const key of formData.keys()) {
                    currentUrl.searchParams.delete(key);
                }

                // Tambahkan nilai baru dari form
                for (const [key, value] of formData.entries()) {
                    if (value) {
                        currentUrl.searchParams.append(key, value);
                    }
                }
                
                // Jika filter berubah, kembalikan ke halaman pertama
                currentUrl.searchParams.delete('page');

                fetchAndUpdate(currentUrl);
            }
        });

        // Prevent default submission
        container.addEventListener('submit', (e) => {
            const form = e.target.closest('.cms-filter-form');
            if (form) {
                e.preventDefault();
                // change event sudah meng-handle, tapi fallback jika tekan enter
                form.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        // Handle Click (Pagination & Sort)
        container.addEventListener('click', (e) => {
            const link = e.target.closest('.pagination a');
            const sortHeader = e.target.closest('[data-cms-sort]');

            // Handle Pagination Link
            if (link && link.href && !link.href.includes('#')) {
                e.preventDefault();
                fetchAndUpdate(new URL(link.href));
                return;
            }

            // Handle Sort Header
            if (sortHeader) {
                e.preventDefault();
                const column = sortHeader.dataset.cmsSort;
                if (!column) return;

                const currentUrl = new URL(window.location.href);
                const currentSort = currentUrl.searchParams.get('sort');
                let newDir = 'desc'; // Default

                if (currentSort === column) {
                    const currentDir = currentUrl.searchParams.get('dir') || 'desc';
                    newDir = currentDir === 'desc' ? 'asc' : 'desc';
                }

                currentUrl.searchParams.set('sort', column);
                currentUrl.searchParams.set('dir', newDir);
                // Kembalikan ke halaman pertama bila sorting berubah
                currentUrl.searchParams.delete('page');

                fetchAndUpdate(currentUrl);
            }
        });
    });
}
