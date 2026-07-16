import 'bootstrap/dist/js/bootstrap.bundle';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

const nav = document.querySelector('.site-nav');
if (nav) window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 12), { passive: true });

const observer = 'IntersectionObserver' in window ? new IntersectionObserver(entries => entries.forEach(entry => {
    if (entry.isIntersecting) { entry.target.classList.add('is-visible'); observer.unobserve(entry.target); }
}), { threshold: .12 }) : null;
document.querySelectorAll('.reveal').forEach(el => observer ? observer.observe(el) : el.classList.add('is-visible'));

document.querySelectorAll('[data-quill]').forEach((element) => {
    const input = document.querySelector(element.dataset.input);
    const uploadUrl = element.dataset.uploadUrl;
    const quill = new Quill(element, {
        theme: 'snow',
        modules: { toolbar: { container: [[{ header: [2, 3, false] }], ['bold', 'italic', 'underline', 'strike'], [{ list: 'ordered' }, { list: 'bullet' }], ['blockquote', 'link', 'image'], ['clean']], handlers: { image: imageHandler } } },
    });
    quill.root.innerHTML = input.value || '';
    quill.on('text-change', () => { input.value = quill.root.innerHTML; });

    function imageHandler() {
        if (!uploadUrl) return;
        const picker = document.createElement('input'); picker.type = 'file'; picker.accept = 'image/jpeg,image/png,image/webp'; picker.click();
        picker.onchange = async () => {
            const file = picker.files?.[0]; if (!file) return;
            const alt = window.prompt('Tuliskan deskripsi singkat gambar (alt text):'); if (!alt) return;
            const form = new FormData(); form.append('image', file); form.append('alt_text', alt);
            try {
                const response = await fetch(uploadUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }, body: form });
                if (!response.ok) throw new Error('Upload gagal');
                const data = await response.json(); const range = quill.getSelection(true); quill.insertEmbed(range.index, 'image', data.url); const images = quill.root.querySelectorAll('img'); const inserted = images[images.length - 1]; if (inserted) inserted.setAttribute('alt', data.alt); quill.setSelection(range.index + 1); input.value = quill.root.innerHTML;
            } catch (error) { window.alert('Gambar gagal diunggah. Pastikan format dan ukuran sesuai.'); }
        };
    }
});
