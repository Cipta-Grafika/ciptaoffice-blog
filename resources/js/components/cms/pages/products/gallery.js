const MAX_IMAGES = 8;

function initCurrentGallerySorting(gallery) {
    const grid = gallery.querySelector('[data-product-gallery-sortable]');
    const fields = gallery.querySelector('[data-product-gallery-order-fields]');
    const status = gallery.querySelector('[data-product-gallery-order-status]');

    if (!grid || !fields || !status) return;

    const initialItems = Array.from(grid.querySelectorAll('[data-product-gallery-item]'));
    if (initialItems.length === 0) return;

    const savedOrder = initialItems.map((item) => item.dataset.galleryToken);
    const requestedOrder = Array.from(fields.querySelectorAll('input[name="gallery_order[]"]'))
        .map((input) => input.value);
    const requestedTokensAreValid = requestedOrder.length === savedOrder.length
        && requestedOrder.every((token) => savedOrder.includes(token))
        && new Set(requestedOrder).size === savedOrder.length;
    const defaultStatus = status.textContent.trim();
    const defaultButtonLabels = new WeakMap();
    const defaultButtonTitles = new WeakMap();
    let draggedItem = null;

    initialItems.forEach((item) => {
        const deleteButton = item.querySelector('.cms-product-gallery-delete');
        if (deleteButton) {
            defaultButtonLabels.set(deleteButton, deleteButton.getAttribute('aria-label'));
            defaultButtonTitles.set(deleteButton, deleteButton.title);
        }
    });

    if (requestedTokensAreValid) {
        const itemsByToken = new Map(initialItems.map((item) => [item.dataset.galleryToken, item]));
        requestedOrder.forEach((token) => grid.append(itemsByToken.get(token)));
    }

    const currentOrder = () => Array.from(grid.querySelectorAll('[data-product-gallery-item]'))
        .map((item) => item.dataset.galleryToken);

    const renderOrder = () => {
        const items = Array.from(grid.querySelectorAll('[data-product-gallery-item]'));
        const order = currentOrder();
        const pending = order.some((token, index) => token !== savedOrder[index]);

        fields.replaceChildren(...order.map((token) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'gallery_order[]';
            input.value = token;

            return input;
        }));

        items.forEach((item, index) => {
            const caption = item.querySelector('figcaption');
            const deleteButton = item.querySelector('.cms-product-gallery-delete');

            item.classList.toggle('is-thumbnail', index === 0);

            if (caption) {
                caption.replaceChildren();

                if (index === 0) {
                    caption.append('Thumbnail');
                } else {
                    const number = document.createElement('span');
                    number.textContent = String(index).padStart(2, '0');
                    caption.append(number, 'Carousel');
                }
            }

            if (deleteButton) {
                deleteButton.disabled = pending;
                deleteButton.setAttribute(
                    'aria-label',
                    pending
                        ? 'Simpan urutan sebelum menghapus gambar'
                        : defaultButtonLabels.get(deleteButton)
                );
                deleteButton.title = pending
                    ? 'Simpan urutan terlebih dahulu'
                    : defaultButtonTitles.get(deleteButton);
            }
        });

        status.textContent = pending
            ? 'Urutan berubah. Klik “Simpan produk” untuk menerapkannya.'
            : defaultStatus;
        status.classList.toggle('is-pending', pending);
    };

    const moveWithKeyboard = (item, direction) => {
        const sibling = direction < 0 ? item.previousElementSibling : item.nextElementSibling;
        if (!sibling) return;

        if (direction < 0) {
            grid.insertBefore(item, sibling);
        } else {
            grid.insertBefore(sibling, item);
        }

        renderOrder();
        item.focus();
    };

    initialItems.forEach((item) => {
        item.addEventListener('dragstart', (event) => {
            if (initialItems.length < 2) {
                event.preventDefault();
                return;
            }

            draggedItem = item;
            item.setAttribute('aria-grabbed', 'true');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', item.dataset.galleryToken);
            window.requestAnimationFrame(() => item.classList.add('is-dragging'));
        });

        item.addEventListener('dragend', () => {
            item.classList.remove('is-dragging');
            item.setAttribute('aria-grabbed', 'false');
            draggedItem = null;
            renderOrder();
        });

        item.addEventListener('keydown', (event) => {
            if (!event.altKey || !['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(event.key)) {
                return;
            }

            event.preventDefault();
            moveWithKeyboard(item, ['ArrowLeft', 'ArrowUp'].includes(event.key) ? -1 : 1);
        });
    });

    grid.addEventListener('dragover', (event) => {
        if (!draggedItem) return;

        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';

        const target = event.target.closest('[data-product-gallery-item]');
        if (!target || target === draggedItem || !grid.contains(target)) return;

        const rect = target.getBoundingClientRect();
        const nearSameRow = Math.abs(event.clientY - (rect.top + rect.height / 2)) < rect.height * 0.35;
        const insertBefore = nearSameRow
            ? event.clientX < rect.left + rect.width / 2
            : event.clientY < rect.top + rect.height / 2;

        grid.insertBefore(draggedItem, insertBefore ? target : target.nextElementSibling);
    });

    grid.addEventListener('drop', (event) => {
        if (!draggedItem) return;

        event.preventDefault();
        renderOrder();
    });

    renderOrder();
}

export function initProductGallery(root = document) {
    root.querySelectorAll('[data-product-gallery]').forEach((gallery) => {
        const input = gallery.querySelector('[data-product-gallery-input]');
        const preview = gallery.querySelector('[data-product-gallery-preview]');
        const current = gallery.querySelector('[data-product-gallery-current]');
        const status = gallery.querySelector('[data-product-gallery-status]');

        if (!input || !preview || !status) return;

        initCurrentGallerySorting(gallery);

        const hasCurrentGallery = gallery.dataset.hasCurrent === 'true';
        let previewUrls = [];

        const clearPreview = () => {
            previewUrls.forEach((url) => URL.revokeObjectURL(url));
            previewUrls = [];
            preview.replaceChildren();
        };

        input.addEventListener('change', () => {
            const files = Array.from(input.files ?? []);
            clearPreview();

            if (files.length === 0) {
                input.setCustomValidity('');
                preview.classList.add('d-none');
                current?.classList.remove('d-none');
                status.textContent = hasCurrentGallery
                    ? 'Tidak ada file baru dipilih. Galeri saat ini tetap digunakan sampai Anda memilih set baru.'
                    : 'Pilih beberapa file sekaligus sesuai urutan tampilan yang diinginkan.';
                return;
            }

            if (files.length > MAX_IMAGES) {
                input.setCustomValidity('Maksimal ' + MAX_IMAGES + ' gambar dalam satu galeri.');
                status.textContent = files.length + ' file dipilih. Kurangi menjadi maksimal ' + MAX_IMAGES + ' gambar.';
            } else {
                input.setCustomValidity('');
                status.textContent = files.length + ' gambar siap disimpan. Gambar pertama akan menjadi thumbnail.';
            }

            files.forEach((file, index) => {
                const figure = document.createElement('figure');
                const image = document.createElement('img');
                const caption = document.createElement('figcaption');
                const url = URL.createObjectURL(file);

                previewUrls.push(url);
                figure.className = 'cms-product-gallery-item' + (index === 0 ? ' is-thumbnail' : '');
                image.src = url;
                image.alt = 'Preview ' + file.name;
                if (index === 0) {
                    caption.append('Thumbnail');
                } else {
                    const order = document.createElement('span');
                    order.textContent = String(index).padStart(2, '0');
                    caption.append(order, 'Carousel');
                }
                figure.append(image, caption);
                preview.append(figure);
            });

            preview.classList.remove('d-none');
            current?.classList.add('d-none');
        });

        window.addEventListener('beforeunload', clearPreview, { once: true });
    });
}
