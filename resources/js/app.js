import 'bootstrap/dist/js/bootstrap.bundle';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

const nav = document.querySelector('.site-nav');
if (nav) window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 12), { passive: true });

const metricStrip = document.querySelector('[data-metric-strip]');
const metricStripSentinel = document.querySelector('[data-metric-strip-sentinel]');
if (nav && metricStrip && metricStripSentinel) {
    const root = document.documentElement;
    const links = [...metricStrip.querySelectorAll('.metric-section-link')];
    const sections = links.map(link => document.querySelector(link.hash));
    let dockThreshold = 0;
    let ticking = false;

    const setDocked = (docked) => {
        if (metricStrip.classList.contains('is-docked') === docked) return;

        metricStrip.classList.toggle('is-docked', docked);
        nav.classList.toggle('has-section-nav', docked);
        links.forEach((link) => {
            link.toggleAttribute('aria-hidden', !docked);
            link.tabIndex = docked ? 0 : -1;
        });
        requestAnimationFrame(() => root.style.setProperty('--metric-nav-height', `${metricStrip.offsetHeight}px`));
    };

    const updateScrollState = () => {
        const navHeight = nav.offsetHeight;
        const docked = window.scrollY >= dockThreshold;
        setDocked(docked);

        const activationLine = navHeight + (docked ? metricStrip.offsetHeight : 0) + 32;
        let activeSection = null;
        sections.forEach((section) => {
            if (section && section.getBoundingClientRect().top <= activationLine) activeSection = section.id;
        });
        links.forEach((link) => {
            const active = docked && link.hash === `#${activeSection}`;
            link.classList.toggle('active', active);
            if (active) link.setAttribute('aria-current', 'location');
            else link.removeAttribute('aria-current');
        });
    };

    const queueScrollUpdate = () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => {
            updateScrollState();
            ticking = false;
        });
    };

    const measureStickyPosition = () => {
        const navHeight = nav.offsetHeight;
        root.style.setProperty('--site-nav-height', `${navHeight}px`);
        dockThreshold = metricStripSentinel.getBoundingClientRect().top + window.scrollY - navHeight;
        root.style.setProperty('--metric-nav-height', `${metricStrip.offsetHeight}px`);
        updateScrollState();
    };

    measureStickyPosition();
    window.addEventListener('scroll', queueScrollUpdate, { passive: true });
    window.addEventListener('resize', measureStickyPosition, { passive: true });
    if ('ResizeObserver' in window) new ResizeObserver(measureStickyPosition).observe(nav);
}

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

document.querySelectorAll('[data-cover-picker]').forEach((picker) => {
    const input = picker.querySelector('[data-cover-input]');
    const preview = picker.querySelector('[data-cover-preview]');
    const placeholder = picker.querySelector('[data-cover-placeholder]');
    const filename = picker.querySelector('[data-cover-filename]');
    const status = picker.querySelector('[data-cover-status]');
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

document.querySelectorAll('[data-quill]').forEach((element) => {
    const input = document.querySelector(element.dataset.input);
    const uploadUrl = element.dataset.uploadUrl;
    const quill = new Quill(element, {
        theme: 'snow',
        modules: { toolbar: { container: [[{ header: [2, 3, false] }], ['bold', 'italic', 'underline', 'strike'], [{ list: 'ordered' }, { list: 'bullet' }], ['blockquote', 'link', 'image'], ['clean']], handlers: { image: imageHandler, list: listHandler } } },
    });
    const initialContent = quill.clipboard.convert({ html: input.value || '' });
    quill.setContents(initialContent, Quill.sources.SILENT);
    const syncInput = () => { input.value = quill.getSemanticHTML(); };
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
        const picker = document.createElement('input'); picker.type = 'file'; picker.accept = 'image/jpeg,image/png,image/webp'; picker.click();
        picker.onchange = async () => {
            const file = picker.files?.[0]; if (!file) return;
            const form = new FormData(); form.append('image', file);
            try {
                const response = await fetch(uploadUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }, body: form });
                if (!response.ok) throw new Error('Upload gagal');
                const data = await response.json(); const range = quill.getSelection(true); quill.insertEmbed(range.index, 'image', data.url); const images = quill.root.querySelectorAll('img'); const inserted = images[images.length - 1]; if (inserted) inserted.setAttribute('alt', data.alt); quill.setSelection(range.index + 1); syncInput();
            } catch (error) { window.alert('Gambar gagal diunggah. Pastikan format dan ukuran sesuai.'); }
        };
    }
});
