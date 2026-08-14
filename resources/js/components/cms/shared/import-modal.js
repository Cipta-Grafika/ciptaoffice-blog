const formatFileSize = (bytes) => {
    if (!Number.isFinite(bytes) || bytes <= 0) return '0 B';

    const units = ['B', 'KB', 'MB', 'GB'];
    const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
    const value = bytes / (1024 ** index);

    return `${value.toFixed(index === 0 || value >= 10 ? 0 : 1)} ${units[index]}`;
};

const containsFiles = (event) => Array.from(event.dataTransfer?.types || []).includes('Files');

const extensionOf = (filename) => {
    const index = filename.lastIndexOf('.');
    return index >= 0 ? filename.slice(index).toLowerCase() : '';
};

const initializeModal = (dialog) => {
    if (dialog.dataset.cmsImportReady === 'true') return;
    dialog.dataset.cmsImportReady = 'true';

    const form = dialog.querySelector('[data-cms-import-form]');
    const dropzone = dialog.querySelector('[data-cms-import-dropzone]');
    const target = dialog.querySelector('[data-cms-import-dropzone-target]');
    const input = dialog.querySelector('[data-cms-import-input]');
    const fileCard = dialog.querySelector('[data-cms-import-file]');
    const filename = dialog.querySelector('[data-cms-import-filename]');
    const fileSize = dialog.querySelector('[data-cms-import-filesize]');
    const feedback = dialog.querySelector('[data-cms-import-feedback]');
    const removeButton = dialog.querySelector('[data-cms-import-remove]');
    const submitButton = dialog.querySelector('[data-cms-import-submit]');
    const submitLabel = dialog.querySelector('[data-cms-import-submit-label]');
    const submitIcon = dialog.querySelector('[data-cms-import-submit-icon]');
    const spinner = dialog.querySelector('[data-cms-import-spinner]');
    const closeButtons = dialog.querySelectorAll('[data-cms-import-close]');
    const acceptedExtensions = (dialog.dataset.acceptedExtensions || '')
        .split(',')
        .map((extension) => extension.trim().toLowerCase())
        .filter(Boolean);
    const maxSize = Number(dialog.dataset.maxSize || 0);
    const serverError = feedback?.textContent.trim() || '';
    let dragDepth = 0;

    if (!form || !dropzone || !target || !input || !fileCard || !submitButton) return;

    const showFeedback = (message) => {
        if (!feedback) return;
        feedback.textContent = message;
        feedback.classList.remove('d-none');
        dropzone.classList.add('has-error');
        input.setAttribute('aria-invalid', 'true');
    };

    const clearFeedback = () => {
        if (!feedback) return;
        feedback.textContent = '';
        feedback.classList.add('d-none');
        dropzone.classList.remove('has-error');
        input.removeAttribute('aria-invalid');
    };

    const reset = ({ preserveFeedback = false } = {}) => {
        input.value = '';
        target.hidden = false;
        fileCard.hidden = true;
        filename.textContent = '';
        fileSize.textContent = '';
        submitButton.disabled = true;
        submitButton.removeAttribute('aria-busy');
        submitLabel.textContent = 'Import artikel';
        submitIcon?.classList.remove('d-none');
        spinner?.classList.add('d-none');
        form.classList.remove('is-submitting');
        dropzone.classList.remove('is-dragging', 'has-file');
        if (!preserveFeedback) clearFeedback();
    };

    const validate = (file) => {
        if (!acceptedExtensions.includes(extensionOf(file.name))) {
            return 'Format file tidak didukung. Gunakan file CSV, XLSX, atau JSON.';
        }

        if (maxSize && file.size > maxSize) {
            return `Ukuran file melebihi batas ${formatFileSize(maxSize)}.`;
        }

        return '';
    };

    const renderFile = (file) => {
        const error = validate(file);

        if (error) {
            reset();
            showFeedback(error);
            return false;
        }

        clearFeedback();
        target.hidden = true;
        fileCard.hidden = false;
        filename.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        submitButton.disabled = false;
        dropzone.classList.add('has-file');
        return true;
    };

    const assignDroppedFile = (file) => {
        const error = validate(file);

        if (error) {
            showFeedback(error);
            return;
        }

        try {
            const transfer = new DataTransfer();
            transfer.items.add(file);
            input.files = transfer.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        } catch {
            showFeedback('Browser ini belum mendukung upload melalui drag-and-drop. Klik area untuk memilih file.');
        }
    };

    input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!file) {
            reset();
            return;
        }

        if (!renderFile(file)) input.value = '';
    });

    removeButton?.addEventListener('click', () => {
        reset();
        input.focus({ preventScroll: true });
    });

    target.addEventListener('dragenter', (event) => {
        if (!containsFiles(event)) return;
        event.preventDefault();
        dragDepth += 1;
        dropzone.classList.add('is-dragging');
    });

    target.addEventListener('dragover', (event) => {
        if (!containsFiles(event)) return;
        event.preventDefault();
        event.dataTransfer.dropEffect = 'copy';
    });

    target.addEventListener('dragleave', (event) => {
        if (!containsFiles(event)) return;
        dragDepth = Math.max(0, dragDepth - 1);
        if (dragDepth === 0) dropzone.classList.remove('is-dragging');
    });

    target.addEventListener('drop', (event) => {
        if (!containsFiles(event)) return;
        event.preventDefault();
        dragDepth = 0;
        dropzone.classList.remove('is-dragging');

        const files = Array.from(event.dataTransfer?.files || []);
        if (files.length !== 1) {
            showFeedback('Pilih satu file untuk setiap proses import.');
            return;
        }

        assignDroppedFile(files[0]);
    });

    closeButtons.forEach((button) => button.addEventListener('click', () => dialog.close()));

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });

    dialog.addEventListener('close', () => {
        if (!form.classList.contains('is-submitting')) reset();
    });

    form.addEventListener('submit', (event) => {
        const file = input.files?.[0];

        if (!file || !renderFile(file)) {
            event.preventDefault();
            showFeedback('Pilih file CSV, XLSX, atau JSON sebelum memulai import.');
            return;
        }

        form.classList.add('is-submitting');
        submitButton.disabled = true;
        submitButton.setAttribute('aria-busy', 'true');
        submitLabel.textContent = 'Mengimpor…';
        submitIcon?.classList.add('d-none');
        spinner?.classList.remove('d-none');
    });

    if (serverError) showFeedback(serverError);
    if (dialog.hasAttribute('data-open-on-load')) {
        requestAnimationFrame(() => dialog.showModal());
    }
};

export function initCmsImportModals(root = document) {
    root.querySelectorAll('[data-cms-import-modal]').forEach(initializeModal);

    if (root.documentElement?.dataset.cmsImportDelegated === 'true') return;
    if (root.documentElement) root.documentElement.dataset.cmsImportDelegated = 'true';

    root.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-cms-import-open]');
        if (!trigger) return;

        const dialog = root.getElementById(trigger.dataset.cmsImportOpen);
        if (!dialog) return;

        event.preventDefault();
        initializeModal(dialog);
        if (!dialog.open) dialog.showModal();
    });
}
