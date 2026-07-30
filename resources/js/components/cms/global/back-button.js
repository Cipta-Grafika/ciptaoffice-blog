export function initCmsBackButtons(root = document) {
    root.querySelectorAll('[data-cms-back]').forEach((button) => {
        button.addEventListener('click', () => {
            let sameOriginReferrer = false;
            try {
                const referrer = new URL(document.referrer);
                const isAuthPage = ['/cms/login', '/forgot-password', '/reset-password'].some(
                    (path) => referrer.pathname.startsWith(path),
                );
                sameOriginReferrer = referrer.origin === window.location.origin && !isAuthPage;
            } catch (error) {}

            if (sameOriginReferrer && window.history.length > 1) window.history.back();
            else window.location.assign(button.dataset.fallbackUrl);
        });
    });
}
