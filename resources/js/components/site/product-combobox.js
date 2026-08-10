const normalize = (value) => value
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .toLocaleLowerCase('id');

export function initProductCombobox(root = document) {
    root.querySelectorAll('[data-product-combobox]').forEach((combobox) => {
        const label = combobox.querySelector('[data-product-combobox-label]');
        const select = combobox.querySelector('[data-product-combobox-select]');
        const enhancedControl = combobox.querySelector('[data-product-combobox-enhanced]');
        const input = combobox.querySelector('[data-product-combobox-input]');
        const clearButton = combobox.querySelector('[data-product-combobox-clear]');
        const listbox = combobox.querySelector('[data-product-combobox-listbox]');
        const optionsContainer = combobox.querySelector('[data-product-combobox-options]');
        const emptyState = combobox.querySelector('[data-product-combobox-empty]');
        const status = combobox.querySelector('[data-product-combobox-status]');

        if (!label || !select || !enhancedControl || !input || !clearButton || !listbox || !optionsContainer || !emptyState || !status) {
            return;
        }

        const options = [...select.options].map((selectOption, index) => {
            const option = document.createElement('button');
            const optionLabel = selectOption.textContent.trim();

            option.type = 'button';
            option.id = `${listbox.id}-option-${index}`;
            option.className = 'product-combobox-option';
            option.dataset.productComboboxOption = '';
            option.dataset.value = selectOption.value;
            option.dataset.search = normalize(optionLabel);
            option.setAttribute('role', 'option');

            const labelElement = document.createElement('span');
            const checkIcon = document.createElement('i');
            labelElement.textContent = optionLabel;
            checkIcon.className = 'bi bi-check2';
            checkIcon.setAttribute('aria-hidden', 'true');
            option.append(labelElement, checkIcon);
            optionsContainer.append(option);

            return option;
        });

        let activeOptionIndex = -1;

        const selectedOption = () => select.options[select.selectedIndex] || select.options[0];
        const visibleOptions = () => options.filter((option) => !option.hidden);

        const syncSelection = () => {
            const selected = selectedOption();
            input.value = selected?.value ? selected.textContent.trim() : '';
            clearButton.hidden = !selected?.value;
            options.forEach((option) => {
                option.setAttribute('aria-selected', String(option.dataset.value === select.value));
            });
        };

        const setActiveOption = (index) => {
            const visible = visibleOptions();
            options.forEach((option) => option.classList.remove('is-active'));

            if (visible.length === 0) {
                activeOptionIndex = -1;
                input.removeAttribute('aria-activedescendant');
                return;
            }

            activeOptionIndex = Math.max(0, Math.min(index, visible.length - 1));
            const activeOption = visible[activeOptionIndex];
            activeOption.classList.add('is-active');
            input.setAttribute('aria-activedescendant', activeOption.id);
            activeOption.scrollIntoView({ block: 'nearest' });
        };

        const filterOptions = (query = '') => {
            const normalizedQuery = normalize(query.trim());
            let resultCount = 0;

            options.forEach((option) => {
                option.hidden = normalizedQuery !== '' && !option.dataset.search.includes(normalizedQuery);
                if (!option.hidden) {
                    resultCount += 1;
                }
            });

            emptyState.hidden = resultCount !== 0;
            status.textContent = resultCount === 0
                ? `Tidak ada produk yang cocok dengan “${query.trim()}”.`
                : `${resultCount} pilihan tersedia.`;
            setActiveOption(0);
        };

        const openListbox = ({ showAll = false } = {}) => {
            filterOptions(showAll ? '' : input.value);
            listbox.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            combobox.classList.add('is-open');
        };

        const closeListbox = ({ restore = false } = {}) => {
            if (restore) {
                syncSelection();
            }
            listbox.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
            combobox.classList.remove('is-open');
            activeOptionIndex = -1;
        };

        const chooseOption = (option) => {
            select.value = option.dataset.value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            syncSelection();
            input.focus();
            closeListbox();
            status.textContent = option.dataset.value
                ? `${option.textContent.trim()} dipilih.`
                : 'Produk terkait dikosongkan.';
        };

        options.forEach((option) => {
            option.addEventListener('click', () => chooseOption(option));
            option.addEventListener('mousemove', () => {
                setActiveOption(visibleOptions().indexOf(option));
            });
        });

        input.addEventListener('focus', () => openListbox({ showAll: true }));
        input.addEventListener('click', () => openListbox({ showAll: true }));
        input.addEventListener('input', () => openListbox());
        input.addEventListener('keydown', (event) => {
            const visible = visibleOptions();

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                if (listbox.hidden) {
                    openListbox({ showAll: true });
                } else {
                    setActiveOption(activeOptionIndex + 1);
                }
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                if (listbox.hidden) {
                    openListbox({ showAll: true });
                    setActiveOption(visibleOptions().length - 1);
                } else {
                    setActiveOption(activeOptionIndex - 1);
                }
            } else if (event.key === 'Enter' && !listbox.hidden && activeOptionIndex >= 0) {
                event.preventDefault();
                chooseOption(visible[activeOptionIndex]);
            } else if (event.key === 'Escape' && !listbox.hidden) {
                event.preventDefault();
                closeListbox({ restore: true });
            }
        });

        clearButton.addEventListener('click', () => {
            select.value = '';
            select.dispatchEvent(new Event('change', { bubbles: true }));
            input.value = '';
            clearButton.hidden = true;
            input.focus();
            openListbox({ showAll: true });
            status.textContent = 'Produk terkait dikosongkan.';
        });

        combobox.addEventListener('focusout', (event) => {
            if (!combobox.contains(event.relatedTarget)) {
                closeListbox({ restore: true });
            }
        });

        label.htmlFor = input.id;
        select.tabIndex = -1;
        select.setAttribute('aria-hidden', 'true');
        input.classList.toggle('is-invalid', select.classList.contains('is-invalid'));
        syncSelection();
        enhancedControl.hidden = false;
        combobox.classList.add('is-enhanced');
    });
}
