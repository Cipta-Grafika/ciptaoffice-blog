export function initArticleShare(root = document) {
    root.querySelectorAll('[data-copy-link]').forEach((button) => {
        let hideTimer;

        button.addEventListener('click', async () => {
            const url = button.dataset.copyLink;
            const status = button.closest('.article-share')?.querySelector('[data-copy-status]');

            clearTimeout(hideTimer);

            try {
                await navigator.clipboard.writeText(url);
                if (status) {
                    status.textContent = 'Tautan disalin.';
                    status.hidden = false;
                }
                button.classList.add('is-copied');
            } catch {
                if (status) {
                    status.textContent = 'Gagal menyalin tautan.';
                    status.hidden = false;
                }
            }

            hideTimer = setTimeout(() => {
                if (status) {
                    status.textContent = '';
                    status.hidden = true;
                }
                button.classList.remove('is-copied');
            }, 3000);
        });
    });
}
