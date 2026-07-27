export function initArticleToc(root = document) {
    root.querySelectorAll('[data-article-toc]').forEach((article) => {
        const content = article.querySelector('[data-article-content]');
        const toc = article.querySelector('.article-toc');
        const list = article.querySelector('[data-article-toc-list]');
        const panel = article.querySelector('[data-article-toc-panel]');
        const toggle = article.querySelector('[data-article-toc-toggle]');

        if (!content || !toc || !list || !panel || !toggle) return;

        const headings = [...content.querySelectorAll('h2, h3')];
        if (headings.length === 0) {
            toc.remove();
            return;
        }

        const headingElements = new Set(headings);
        const usedIds = new Set(
            [...root.querySelectorAll('[id]')]
                .filter((element) => !headingElements.has(element))
                .map((element) => element.id),
        );
        const slugify = (value) => value
            .normalize('NFKD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');

        headings.forEach((heading, index) => {
            const baseId = heading.id || slugify(heading.textContent) || `bagian-${index + 1}`;
            let headingId = baseId;
            let suffix = 2;
            while (usedIds.has(headingId)) headingId = `${baseId}-${suffix++}`;
            heading.id = headingId;
            usedIds.add(headingId);

            const item = document.createElement('li');
            const link = document.createElement('a');
            item.className = `article-toc-item article-toc-item--${heading.tagName.toLowerCase()}`;
            link.href = `#${headingId}`;
            link.textContent = heading.textContent.trim();
            link.dataset.articleTocLink = '';
            item.append(link);
            list.append(item);
        });

        const links = [...list.querySelectorAll('[data-article-toc-link]')];
        const desktop = window.matchMedia('(min-width: 1200px)');
        let open = false;
        let ticking = false;

        const applyPanelState = () => {
            const expanded = desktop.matches || open;
            toc.classList.toggle('is-open', open && !desktop.matches);
            toggle.setAttribute('aria-expanded', String(expanded));
            toggle.querySelector('.visually-hidden').textContent = expanded
                ? 'Tutup daftar isi'
                : 'Buka daftar isi';
            panel.toggleAttribute('inert', !expanded);
            panel.setAttribute('aria-hidden', String(!expanded));
        };

        const setActiveLink = () => {
            const activationLine = Math.max(112, window.innerHeight * .22);
            let activeHeading = headings[0];
            headings.forEach((heading) => {
                if (heading.getBoundingClientRect().top <= activationLine) activeHeading = heading;
            });
            links.forEach((link) => {
                const active = link.hash === `#${activeHeading.id}`;
                link.classList.toggle('active', active);
                if (active) link.setAttribute('aria-current', 'location');
                else link.removeAttribute('aria-current');
            });
        };

        const queueActiveLinkUpdate = () => {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(() => {
                setActiveLink();
                ticking = false;
            });
        };

        toggle.addEventListener('click', () => {
            open = !open;
            applyPanelState();
        });
        links.forEach((link) => link.addEventListener('click', () => {
            if (!desktop.matches) {
                open = false;
                applyPanelState();
            }
        }));
        document.addEventListener('click', (event) => {
            if (!desktop.matches && open && !toc.contains(event.target)) {
                open = false;
                applyPanelState();
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && open) {
                open = false;
                applyPanelState();
                toggle.focus();
            }
        });
        desktop.addEventListener('change', () => {
            open = false;
            applyPanelState();
        });
        window.addEventListener('scroll', queueActiveLinkUpdate, { passive: true });
        applyPanelState();
        setActiveLink();
    });
}
