import Quill from 'quill';
import 'quill/dist/quill.snow.css';

export function initQuillEditors(root = document) {
    root.querySelectorAll('[data-quill]').forEach((element) => {
        const input = root.querySelector(element.dataset.input);
        if (!input) return;
        const uploadUrl = element.dataset.uploadUrl;
        const quill = new Quill(element, {
            theme: 'snow',
            modules: {
                toolbar: {
                    container: [
                        [{ header: 1 }, { header: 2 }, { header: 3 }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [
                            { list: 'bullet' },
                            { list: 'ordered' },
                            { indent: '-1' },
                            { indent: '+1' },
                        ],
                        [
                            { align: '' },
                            { align: 'center' },
                            { align: 'right' },
                            { align: 'justify' },
                        ],
                        ['blockquote', 'link', 'image'],
                        ['clean'],
                    ],
                    handlers: {
                        image: imageHandler,
                        list: listHandler,
                    },
                },
            },
        });
        const initialContent = quill.clipboard.convert({ html: input.value || '' });
        quill.setContents(initialContent, Quill.sources.SILENT);
        const syncInput = () => {
            input.value = quill.getSemanticHTML();
        };
        quill.on('text-change', syncInput);

        function listHandler(value) {
            const range = quill.getSelection();
            if (!range) return;

            quill.getLines(range.index, Math.max(range.length, 1)).forEach((line) => {
                const lineIndex = quill.getIndex(line);
                const formats = quill.getFormat(lineIndex, line.length());
                if (formats.header) return;

                quill.formatLine(lineIndex, line.length(), 'list', value, Quill.sources.USER);
            });
            quill.setSelection(range.index, range.length, Quill.sources.SILENT);
        }

        function imageHandler() {
            if (!uploadUrl) return;
            const picker = document.createElement('input');
            picker.type = 'file';
            picker.accept = 'image/jpeg,image/png,image/webp';
            picker.click();
            picker.onchange = async () => {
                const file = picker.files?.[0];
                if (!file) return;
                const form = new FormData();
                form.append('image', file);
                try {
                    const response = await fetch(uploadUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            Accept: 'application/json',
                        },
                        body: form,
                    });
                    if (!response.ok) throw new Error('Upload gagal');
                    const data = await response.json();
                    const range = quill.getSelection(true);
                    quill.insertEmbed(range.index, 'image', data.url);
                    const images = quill.root.querySelectorAll('img');
                    const inserted = images[images.length - 1];
                    if (inserted) inserted.setAttribute('alt', data.alt);
                    quill.setSelection(range.index + 1);
                    syncInput();
                } catch (error) {
                    window.alert('Gambar gagal diunggah. Pastikan format dan ukuran sesuai.');
                }
            };
        }
    });
}
