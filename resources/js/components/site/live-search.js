export function initLiveSearch(root = document) {
    root.querySelectorAll('[data-live-search-form]').forEach((form) => {
        const input = form.querySelector('[data-live-search-input]');
        const clearButton = form.querySelector('[data-live-search-clear]');
        const parameterInputs = [...form.querySelectorAll('[data-live-search-param]')];
        const catalog = form.parentElement.querySelector('[data-live-search-results]');
        const status = form.parentElement.querySelector('[data-live-search-status]');

        if (!input || !clearButton || !catalog || !status) {
            return;
        }

        const delay = Number(form.dataset.liveSearchDelay) || 500;
        let timeoutId;
        let composing = false;
        let activeController;

        const updateClearButton = () => {
            clearButton.hidden = input.value.length === 0;
        };

        const setLoading = (isLoading) => {
            catalog.classList.toggle('is-loading', isLoading);
            catalog.setAttribute('aria-busy', String(isLoading));
            form.classList.toggle('is-loading', isLoading);
        };

        const setStatus = (message) => {
            status.textContent = message;
        };

        const syncForm = (url) => {
            const query = url.searchParams.get('q') || '';

            input.value = query;
            input.defaultValue = query;
            parameterInputs.forEach((parameterInput) => {
                const value = url.searchParams.get(parameterInput.name) || '';
                parameterInput.value = value;
                parameterInput.disabled = !value;
            });
            updateClearButton();
        };

        const buildSearchUrl = () => {
            const url = new URL(form.action, window.location.href);
            const formData = new FormData(form);

            formData.forEach((value, key) => {
                const normalizedValue = String(value).trim();
                if (normalizedValue) {
                    url.searchParams.set(key, normalizedValue);
                }
            });

            return url;
        };

        const fetchCatalog = async (url, { historyMode = 'replace' } = {}) => {
            activeController?.abort();
            const controller = new AbortController();
            activeController = controller;
            setLoading(true);
            setStatus('Memperbarui hasil...');

            try {
                const response = await fetch(url, {
                    headers: {
                        Accept: 'text/html',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: controller.signal,
                });

                if (!response.ok) {
                    throw new Error(`Pencarian gagal dengan status ${response.status}.`);
                }

                const html = await response.text();
                if (controller !== activeController) {
                    return;
                }

                catalog.innerHTML = html;
                syncForm(url);
                if (historyMode === 'push') {
                    window.history.pushState({}, '', url);
                } else if (historyMode === 'replace') {
                    window.history.replaceState({}, '', url);
                }
                setStatus('Hasil berhasil diperbarui.');
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Gagal memperbarui hasil.', error);
                    setStatus('Hasil gagal diperbarui. Silakan coba lagi.');
                }
            } finally {
                if (controller === activeController) {
                    activeController = undefined;
                    setLoading(false);
                }
            }
        };

        const submitSearch = () => {
            timeoutId = undefined;
            fetchCatalog(buildSearchUrl());
        };

        const scheduleSubmit = () => {
            window.clearTimeout(timeoutId);
            if (composing) {
                return;
            }

            timeoutId = window.setTimeout(submitSearch, delay);
        };

        input.addEventListener('input', () => {
            activeController?.abort();
            activeController = undefined;
            setLoading(false);
            updateClearButton();
            scheduleSubmit();
        });

        input.addEventListener('compositionstart', () => {
            composing = true;
            window.clearTimeout(timeoutId);
        });

        input.addEventListener('compositionend', () => {
            composing = false;
            updateClearButton();
            scheduleSubmit();
        });

        clearButton.addEventListener('click', () => {
            window.clearTimeout(timeoutId);
            input.value = '';
            updateClearButton();
            input.focus();
            fetchCatalog(buildSearchUrl());
        });

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            window.clearTimeout(timeoutId);
            fetchCatalog(buildSearchUrl());
        });

        catalog.addEventListener('click', (event) => {
            const link = event.target.closest('[data-live-search-link], .pagination a');
            if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            const url = new URL(link.href, window.location.href);
            if (url.origin !== window.location.origin) {
                return;
            }

            event.preventDefault();
            syncForm(url);
            fetchCatalog(url, { historyMode: 'push' });
        });

        window.addEventListener('popstate', () => {
            const url = new URL(window.location.href);
            syncForm(url);
            fetchCatalog(url, { historyMode: false });
        });
    });
}
