function hasViteDevClient(root = document) {
    return Boolean(root.querySelector('script[src*="/@vite/client"]'));
}

export function prepareViteForHistoryNavigation(root = document) {
    if (!hasViteDevClient(root)) return;

    // Vite uses beforeunload to distinguish navigation from a lost HMR socket.
    // Chrome may skip the native event while freezing a page for BFCache.
    window.dispatchEvent(new Event('beforeunload'));
}

export function initViteBfcacheRecovery(root = document) {
    if (!hasViteDevClient(root)) return;

    window.addEventListener('pageshow', (event) => {
        if (event.persisted) window.location.reload();
    });
}
