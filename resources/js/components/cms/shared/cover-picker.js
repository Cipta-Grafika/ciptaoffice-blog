export function initCoverPickers(root = document) {
    root.querySelectorAll('[data-cover-picker]').forEach((picker) => {
        const input = picker.querySelector('[data-cover-input]');
        const preview = picker.querySelector('[data-cover-preview]');
        const placeholder = picker.querySelector('[data-cover-placeholder]');
        const filename = picker.querySelector('[data-cover-filename]');
        const status = picker.querySelector('[data-cover-status]');
        const removeBtn = picker.querySelector('[data-cover-remove]');
        const removeInput = picker.querySelector('[data-cover-remove-input]');
        
        if (!input || !preview || !filename || !status) return;
        let previewUrl;

        removeBtn?.addEventListener('click', () => {
            input.value = '';
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = undefined;
            
            preview.classList.add('d-none');
            placeholder?.classList.remove('d-none');
            filename.textContent = 'Tidak ada file dipilih.';
            status.textContent = 'Cover akan dihapus saat disimpan.';
            removeBtn.classList.add('d-none');
            if (removeInput) removeInput.value = '1';
        });

        input?.addEventListener('change', () => {
            const file = input.files?.[0];
            if (previewUrl) URL.revokeObjectURL(previewUrl);

            if (!file) {
                previewUrl = undefined;
                preview.src = preview.dataset.currentSrc || '';
                
                const hasOriginalCover = Boolean(preview.dataset.currentSrc);
                preview.classList.toggle('d-none', !hasOriginalCover);
                placeholder?.classList.toggle('d-none', hasOriginalCover);
                filename.textContent = 'Tidak ada file baru dipilih.';
                status.textContent = status.dataset.currentStatus;
                
                removeBtn?.classList.toggle('d-none', !hasOriginalCover);
                if (removeInput) removeInput.value = '0';
                return;
            }

            previewUrl = URL.createObjectURL(file);
            preview.src = previewUrl;
            preview.alt = `Preview ${file.name}`;
            preview.classList.remove('d-none');
            placeholder?.classList.add('d-none');
            filename.textContent = file.name;
            status.textContent = 'Cover baru siap disimpan.';
            
            removeBtn?.classList.remove('d-none');
            if (removeInput) removeInput.value = '0';
        });
    });
}
