import 'bootstrap/dist/js/bootstrap.bundle';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

const nav = document.querySelector('.site-nav');
if (nav) window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 12), { passive: true });

const observer = 'IntersectionObserver' in window ? new IntersectionObserver(entries => entries.forEach(entry => {
    if (entry.isIntersecting) { entry.target.classList.add('is-visible'); observer.unobserve(entry.target); }
}), { threshold: .12 }) : null;
document.querySelectorAll('.reveal').forEach(el => observer ? observer.observe(el) : el.classList.add('is-visible'));

const cmsSidebarCollapse = document.querySelector('[data-cms-sidebar-collapse]');
if (cmsSidebarCollapse) {
    const cmsSidebarBrand = document.querySelector('[data-cms-sidebar-brand]');
    const breakpoint = window.matchMedia('(min-width: 768px)');
    const storageKey = 'cms-sidebar-collapsed';
    const icon = cmsSidebarCollapse.querySelector('i');

    const storedPreference = () => {
        try { return localStorage.getItem(storageKey) === 'true'; } catch (error) { return false; }
    };

    const applySidebarState = (collapsed) => {
        const isCollapsed = collapsed && breakpoint.matches;
        document.documentElement.classList.toggle('cms-sidebar-collapsed', isCollapsed);
        cmsSidebarCollapse.setAttribute('aria-expanded', String(!isCollapsed));
        cmsSidebarCollapse.setAttribute('aria-label', isCollapsed ? 'Perluas sidebar' : 'Perkecil sidebar');
        if (isCollapsed) {
            cmsSidebarBrand?.setAttribute('aria-label', 'Perluas sidebar');
            cmsSidebarBrand?.setAttribute('aria-expanded', 'false');
            cmsSidebarBrand?.setAttribute('title', 'Perluas sidebar');
        } else {
            cmsSidebarBrand?.removeAttribute('aria-label');
            cmsSidebarBrand?.removeAttribute('aria-expanded');
            cmsSidebarBrand?.removeAttribute('title');
        }
        icon?.classList.toggle('bi-chevron-left', !isCollapsed);
        icon?.classList.toggle('bi-chevron-right', isCollapsed);
    };

    applySidebarState(storedPreference());
    cmsSidebarCollapse.addEventListener('click', () => {
        const collapsed = !document.documentElement.classList.contains('cms-sidebar-collapsed');
        try { localStorage.setItem(storageKey, String(collapsed)); } catch (error) {}
        applySidebarState(collapsed);
    });
    cmsSidebarBrand?.addEventListener('click', (event) => {
        if (!breakpoint.matches || !document.documentElement.classList.contains('cms-sidebar-collapsed')) return;
        event.preventDefault();
        try { localStorage.setItem(storageKey, 'false'); } catch (error) {}
        applySidebarState(false);
        cmsSidebarBrand.focus({ preventScroll: true });
    });
    breakpoint.addEventListener('change', () => applySidebarState(storedPreference()));
}

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    const input = document.querySelector(button.dataset.passwordToggle);
    if (!input) return;

    button.addEventListener('click', () => {
        const shouldShow = input.type === 'password';
        const icon = button.querySelector('i');

        input.type = shouldShow ? 'text' : 'password';
        button.setAttribute('aria-label', shouldShow ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        button.setAttribute('aria-pressed', String(shouldShow));
        icon?.classList.toggle('bi-eye', !shouldShow);
        icon?.classList.toggle('bi-eye-slash', shouldShow);
        input.focus({ preventScroll: true });
    });
});

document.querySelectorAll('[data-quill]').forEach((element) => {
    const input = document.querySelector(element.dataset.input);
    const uploadUrl = element.dataset.uploadUrl;
    const quill = new Quill(element, {
        theme: 'snow',
        modules: { toolbar: { container: [[{ header: [2, 3, false] }], ['bold', 'italic', 'underline', 'strike'], [{ list: 'ordered' }, { list: 'bullet' }], ['blockquote', 'link', 'image'], ['clean']], handlers: { image: imageHandler } } },
    });
    const initialContent = quill.clipboard.convert({ html: input.value || '' });
    quill.setContents(initialContent, Quill.sources.SILENT);
    quill.on('text-change', () => { input.value = quill.root.innerHTML; });

    function imageHandler() {
        if (!uploadUrl) return;
        const picker = document.createElement('input'); picker.type = 'file'; picker.accept = 'image/jpeg,image/png,image/webp'; picker.click();
        picker.onchange = async () => {
            const file = picker.files?.[0]; if (!file) return;
            const form = new FormData(); form.append('image', file);
            try {
                const response = await fetch(uploadUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }, body: form });
                if (!response.ok) throw new Error('Upload gagal');
                const data = await response.json(); const range = quill.getSelection(true); quill.insertEmbed(range.index, 'image', data.url); const images = quill.root.querySelectorAll('img'); const inserted = images[images.length - 1]; if (inserted) inserted.setAttribute('alt', data.alt); quill.setSelection(range.index + 1); input.value = quill.root.innerHTML;
            } catch (error) { window.alert('Gambar gagal diunggah. Pastikan format dan ukuran sesuai.'); }
        };
    }
});
