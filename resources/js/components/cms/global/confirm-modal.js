const variants = new Set(['danger', 'warning']);

export function initCmsConfirmModal(root = document) {
    const modal = root.querySelector('[data-cms-confirm-modal]');
    const forms = root.querySelectorAll('[data-cms-confirm-form]');

    if (!modal || !forms.length) return;

    const title = modal.querySelector('[data-cms-confirm-title]');
    const message = modal.querySelector('[data-cms-confirm-message]');
    const submitButton = modal.querySelector('[data-cms-confirm-submit]');
    const submitLabel = modal.querySelector('[data-cms-confirm-submit-label]');
    const closeButtons = modal.querySelectorAll('[data-cms-confirm-close]');

    if (!title || !message || !submitButton || !submitLabel || !closeButtons.length) return;

    const confirmedForms = new WeakSet();
    let pendingForm = null;
    let pendingSubmitter = null;
    let closeTimer = null;

    const clearPending = () => {
        pendingForm = null;
        pendingSubmitter = null;
        submitButton.disabled = false;
        modal.classList.remove('is-closing');
    };

    const closeModal = () => {
        if (!modal.open || modal.classList.contains('is-closing')) return;

        modal.classList.add('is-closing');
        window.clearTimeout(closeTimer);
        closeTimer = window.setTimeout(() => modal.close('cancel'), 180);
    };

    const openModal = (form, submitter) => {
        const variant = variants.has(form.dataset.confirmVariant)
            ? form.dataset.confirmVariant
            : 'danger';

        title.textContent = form.dataset.confirmTitle || 'Konfirmasi tindakan';
        message.textContent = form.dataset.confirmMessage || 'Tindakan ini tidak dapat dibatalkan.';
        submitLabel.textContent = form.dataset.confirmAction || 'Lanjutkan';
        modal.dataset.confirmVariant = variant;
        pendingForm = form;
        pendingSubmitter = submitter;

        if (typeof modal.showModal !== 'function') {
            if (window.confirm(`${title.textContent}\n\n${message.textContent}`)) {
                confirmedForms.add(form);
                form.requestSubmit(submitter || undefined);
            }
            clearPending();
            return;
        }

        modal.showModal();
        window.requestAnimationFrame(() => closeButtons[0].focus());
    };

    forms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (confirmedForms.has(form)) {
                confirmedForms.delete(form);
                return;
            }

            event.preventDefault();
            openModal(form, event.submitter);
        });
    });

    closeButtons.forEach((button) => button.addEventListener('click', closeModal));

    modal.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeModal();
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    modal.addEventListener('close', () => {
        window.clearTimeout(closeTimer);
        clearPending();
    });

    submitButton.addEventListener('click', () => {
        if (!pendingForm?.isConnected) {
            modal.close('cancel');
            return;
        }

        const form = pendingForm;
        const submitter = pendingSubmitter;

        submitButton.disabled = true;
        confirmedForms.add(form);
        modal.close('confirm');
        form.requestSubmit(submitter || undefined);
    });
}
