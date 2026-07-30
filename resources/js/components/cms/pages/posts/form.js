import { initCoverPickers } from '../../shared/cover-picker';

export async function init() {
    initCoverPickers();

    if (!document.querySelector('[data-quill]')) return;

    const { initQuillEditors } = await import('./quill-editor');
    initQuillEditors();
}
