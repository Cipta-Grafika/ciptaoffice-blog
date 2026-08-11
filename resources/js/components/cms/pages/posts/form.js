export async function init() {
    if (!document.querySelector('[data-quill]')) return;

    const { initQuillEditors } = await import('./quill-editor');
    initQuillEditors();
}
