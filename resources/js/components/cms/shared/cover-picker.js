export function initCoverPickers(root = document) {
    root.querySelectorAll('[data-cover-picker]').forEach((picker) => {
        const input = picker.querySelector('[data-cover-input]');
        const preview = picker.querySelector('[data-cover-preview]');
        const placeholder = picker.querySelector('[data-cover-placeholder]');
        const filename = picker.querySelector('[data-cover-filename]');
        const status = picker.querySelector('[data-cover-status]');
        if (!input || !preview || !filename || !status) return;
        let previewUrl;

        input?.addEventListener('change', () => {
            const file = input.files?.[0];
            if (previewUrl) URL.revokeObjectURL(previewUrl);

            if (!file) {
                previewUrl = undefined;
                preview.src = preview.dataset.currentSrc || '';
                preview.classList.toggle('d-none', !preview.dataset.currentSrc);
                placeholder?.classList.toggle('d-none', Boolean(preview.dataset.currentSrc));
                filename.textContent = 'Tidak ada file baru dipilih.';
                status.textContent = status.dataset.currentStatus;
                return;
            }

            previewUrl = URL.createObjectURL(file);
            preview.src = previewUrl;
            preview.alt = `Preview ${file.name}`;
            preview.classList.remove('d-none');
            placeholder?.classList.add('d-none');
            filename.textContent = file.name;
            status.textContent = 'Cover baru siap disimpan.';
        });
    });
}
