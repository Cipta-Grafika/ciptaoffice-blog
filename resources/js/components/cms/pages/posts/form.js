export async function init() {
    document.getElementById('post-form')?.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') return;
        if (!event.target.matches?.('input:not([type="submit"], [type="button"], [type="reset"])')) return;
        event.preventDefault();
    });
    document.querySelectorAll('[data-character-count]').forEach((field) => {
        const output = document.querySelector(field.dataset.characterCount);
        const limit = Number.parseInt(field.dataset.characterLimit, 10);

        if (!output || !Number.isFinite(limit)) return;

        const numberFormatter = new Intl.NumberFormat('id-ID');
        const updateCount = () => {
            const length = Array.from(field.value).length;
            const isOverLimit = length > limit;

            output.textContent = `${numberFormatter.format(length)} / ${numberFormatter.format(limit)} karakter`;
            output.classList.toggle('is-over-limit', isOverLimit);
            field.classList.toggle('is-over-limit', isOverLimit);
            field.setCustomValidity(isOverLimit ? `Ringkasan tidak boleh lebih dari ${numberFormatter.format(limit)} karakter.` : '');
        };

        field.addEventListener('input', updateCount);
        updateCount();
    });

    if (!document.querySelector('[data-quill]')) return;

    const { initQuillEditors } = await import('./quill-editor');
    initQuillEditors();
}
