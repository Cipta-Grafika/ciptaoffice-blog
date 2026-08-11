const formatFileSize = (bytes) => {
    if (!Number.isFinite(bytes) || bytes <= 0) return '';

    const units = ['B', 'KB', 'MB', 'GB'];
    const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
    const value = bytes / (1024 ** index);

    return `${value.toFixed(index === 0 || value >= 10 ? 0 : 1)} ${units[index]}`;
};

const containsFiles = (event) => Array.from(event.dataTransfer?.types || []).includes('Files');

export function initImageDropzones(root = document) {
    root.querySelectorAll('[data-image-dropzone]').forEach((dropzone) => {
        if (dropzone.dataset.imageDropzoneReady === 'true') return;
        dropzone.dataset.imageDropzoneReady = 'true';

        const target = dropzone.querySelector('[data-image-dropzone-target]');
        const input = dropzone.querySelector('[data-image-dropzone-input]');
        const preview = dropzone.querySelector('[data-image-dropzone-preview]');
        const placeholder = dropzone.querySelector('[data-image-dropzone-placeholder]');
        const filename = dropzone.querySelector('[data-image-dropzone-filename]');
        const fileSize = dropzone.querySelector('[data-image-dropzone-file-size]');
        const status = dropzone.querySelector('[data-image-dropzone-status]');
        const action = dropzone.querySelector('[data-image-dropzone-action]');
        const feedback = dropzone.querySelector('[data-image-dropzone-feedback]');
        const removeButton = dropzone.querySelector('[data-image-dropzone-remove]');
        const removeInput = dropzone.querySelector('[data-image-dropzone-remove-input]');

        if (!target || !input || !preview || !filename || !status) return;

        const currentSrc = preview.dataset.currentSrc || '';
        const currentAlt = preview.alt;
        const hasCurrentImage = Boolean(currentSrc);
        const canRemoveCurrent = Boolean(removeInput);
        const acceptedTypes = input.accept.split(',').map((type) => type.trim()).filter(Boolean);
        const maxSize = Number(dropzone.dataset.maxSize || 0);
        const initialFeedback = feedback?.textContent.trim() || '';
        let previewUrl;
        let dragDepth = 0;

        const clearFeedback = () => {
            if (!feedback) return;
            feedback.textContent = '';
            feedback.classList.add('d-none');
            dropzone.classList.remove('has-error');
            input.removeAttribute('aria-invalid');
        };

        const showFeedback = (message) => {
            if (!feedback) return;
            feedback.textContent = message;
            feedback.classList.remove('d-none');
            dropzone.classList.add('has-error');
            input.setAttribute('aria-invalid', 'true');
        };

        const releasePreview = () => {
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = undefined;
        };

        const restoreOriginal = () => {
            releasePreview();
            input.value = '';
            preview.src = currentSrc;
            preview.alt = currentAlt;
            preview.classList.toggle('d-none', !hasCurrentImage);
            placeholder?.classList.toggle('d-none', hasCurrentImage);
            filename.textContent = hasCurrentImage ? 'Tidak ada file baru dipilih.' : 'Tidak ada file dipilih.';
            if (fileSize) fileSize.textContent = '';
            status.textContent = status.dataset.currentStatus || dropzone.dataset.emptyStatus;
            action.textContent = hasCurrentImage ? dropzone.dataset.replaceLabel : dropzone.dataset.chooseLabel;
            removeButton?.classList.toggle('d-none', !hasCurrentImage || !canRemoveCurrent);
            if (removeInput) removeInput.value = '0';
            clearFeedback();
        };

        const validateFile = (file) => {
            const acceptsType = acceptedTypes.some((type) => type === file.type || (type.endsWith('/*') && file.type.startsWith(type.slice(0, -1))));
            if (!acceptsType) {
                return 'Format tidak didukung. Gunakan file JPEG, PNG, atau WebP.';
            }
            if (maxSize && file.size > maxSize) {
                return `Ukuran file melebihi batas ${formatFileSize(maxSize)}.`;
            }
            return '';
        };

        const renderFile = (file) => {
            const error = validateFile(file);
            if (error) {
                restoreOriginal();
                showFeedback(error);
                return false;
            }

            clearFeedback();
            releasePreview();
            previewUrl = URL.createObjectURL(file);
            preview.src = previewUrl;
            preview.alt = `Preview ${file.name}`;
            preview.classList.remove('d-none');
            placeholder?.classList.add('d-none');
            filename.textContent = file.name;
            if (fileSize) fileSize.textContent = `· ${formatFileSize(file.size)}`;
            status.textContent = dropzone.dataset.newStatus;
            action.textContent = dropzone.dataset.replaceLabel;
            removeButton?.classList.remove('d-none');
            if (removeInput) removeInput.value = '0';
            return true;
        };

        const assignDroppedFile = (file) => {
            const error = validateFile(file);
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
                restoreOriginal();
                return;
            }

            if (!renderFile(file)) input.value = '';
        });

        removeButton?.addEventListener('click', () => {
            if (hasCurrentImage && !canRemoveCurrent) {
                restoreOriginal();
                return;
            }

            releasePreview();
            input.value = '';
            preview.src = '';
            preview.classList.add('d-none');
            placeholder?.classList.remove('d-none');
            filename.textContent = 'Tidak ada file dipilih.';
            if (fileSize) fileSize.textContent = '';
            action.textContent = dropzone.dataset.chooseLabel;
            removeButton.classList.add('d-none');
            clearFeedback();

            if (hasCurrentImage && canRemoveCurrent) {
                status.textContent = dropzone.dataset.removedStatus;
                if (removeInput) removeInput.value = '1';
            } else {
                status.textContent = dropzone.dataset.emptyStatus;
                if (removeInput) removeInput.value = '0';
            }
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
            if (files.length > 1) {
                showFeedback('Pilih satu gambar saja untuk setiap unggahan.');
                return;
            }
            if (files[0]) assignDroppedFile(files[0]);
        });

        if (initialFeedback) showFeedback(initialFeedback);
    });
}
