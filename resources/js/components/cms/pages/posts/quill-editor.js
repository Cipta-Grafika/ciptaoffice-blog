import Quill from 'quill';
import TableUp, {
    defaultCustomSelect,
    TableMenuContextmenu,
    TableResizeBox,
    TableResizeScale,
    TableSelection,
} from 'quill-table-up';
import 'quill/dist/quill.snow.css';
import 'quill-table-up/index.css';
import 'quill-table-up/table-creator.css';

Quill.register({ [`modules/${TableUp.moduleName}`]: TableUp }, true);

const spreadsheetColumnLabel = (index) => {
    let label = '';
    let value = index + 1;

    while (value > 0) {
        value -= 1;
        label = String.fromCharCode(65 + (value % 26)) + label;
        value = Math.floor(value / 26);
    }

    return label;
};

class SpreadsheetTableResize extends TableResizeBox {
    constructor(...args) {
        super(...args);
        this.size = 24;
    }

    show() {
        super.show();
        if (!this.root) return;

        this.root.classList.add('cms-spreadsheet-resizer');
        this.root.querySelectorAll('.table-up-resize-box__col-header').forEach((header, index) => {
            const label = spreadsheetColumnLabel(index);
            header.dataset.label = label;
            header.setAttribute('role', 'button');
            header.setAttribute('aria-label', `Pilih kolom ${label}`);
            header.setAttribute('title', `Kolom ${label} · tarik batas untuk mengubah lebar`);
            header.tabIndex = 0;
            header.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' && event.key !== ' ') return;
                event.preventDefault();
                header.click();
            });
        });
        this.root.querySelectorAll('.table-up-resize-box__row-header').forEach((header, index) => {
            const label = String(index + 1);
            header.dataset.label = label;
            header.setAttribute('role', 'button');
            header.setAttribute('aria-label', `Pilih baris ${label}`);
            header.setAttribute('title', `Baris ${label} · tarik batas untuk mengubah tinggi`);
            header.tabIndex = 0;
            header.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' && event.key !== ' ') return;
                event.preventDefault();
                header.click();
            });
        });

        if (this.corner) {
            this.corner.setAttribute('role', 'button');
            this.corner.setAttribute('aria-label', 'Pilih seluruh tabel');
            this.corner.setAttribute('title', 'Pilih seluruh tabel');
            this.corner.tabIndex = 0;
            this.corner.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' && event.key !== ' ') return;
                event.preventDefault();
                this.corner.click();
            });
        }
    }
}

const tableTexts = {
    fullCheckboxText: 'Gunakan lebar penuh',
    customBtnText: 'Ukuran lain',
    confirmText: 'Buat tabel',
    cancelText: 'Batal',
    rowText: 'Baris',
    colText: 'Kolom',
    notPositiveNumberError: 'Masukkan bilangan bulat positif',
    custom: 'Ukuran lain',
    clear: 'Hapus warna',
    transparent: 'Transparan',
    perWidthInsufficient: 'Lebar persentase tidak mencukupi. Ubah tabel menjadi lebar tetap?',
    InsertTop: 'Tambah baris di atas',
    InsertRight: 'Tambah kolom di kanan',
    InsertBottom: 'Tambah baris di bawah',
    InsertLeft: 'Tambah kolom di kiri',
    MergeCell: 'Gabungkan sel',
    SplitCell: 'Pisahkan sel',
    DeleteRow: 'Hapus baris',
    DeleteColumn: 'Hapus kolom',
    DeleteTable: 'Hapus tabel',
};

const tableMenuIcon = (label) => () => {
    const icon = document.createElement('span');
    icon.className = 'table-menu-symbol';
    icon.textContent = label;
    icon.setAttribute('aria-hidden', 'true');
    return icon;
};

const tableMenuItems = [
    {
        name: 'InsertTop',
        icon: tableMenuIcon('↑+'),
        tip: 'Insert row above',
        handle: (table, cells) => {
            table.appendRow(cells, false);
            table.hideTableTools();
        },
    },
    {
        name: 'InsertRight',
        icon: tableMenuIcon('+→'),
        tip: 'Insert column right',
        handle: (table, cells) => {
            table.appendCol(cells, true);
            table.hideTableTools();
        },
    },
    {
        name: 'InsertBottom',
        icon: tableMenuIcon('↓+'),
        tip: 'Insert row below',
        handle: (table, cells) => {
            table.appendRow(cells, true);
            table.hideTableTools();
        },
    },
    {
        name: 'InsertLeft',
        icon: tableMenuIcon('+←'),
        tip: 'Insert column left',
        handle: (table, cells) => {
            table.appendCol(cells, false);
            table.hideTableTools();
        },
    },
    { name: 'break' },
    {
        name: 'MergeCell',
        icon: tableMenuIcon('↔'),
        tip: 'Merge cells',
        handle: (table, cells) => {
            table.mergeCells(cells);
            table.hideTableTools();
        },
    },
    {
        name: 'SplitCell',
        icon: tableMenuIcon('↤↦'),
        tip: 'Split cell',
        handle: (table, cells) => {
            table.splitCell(cells);
            table.hideTableTools();
        },
    },
    { name: 'break' },
    {
        name: 'DeleteRow',
        icon: tableMenuIcon('−R'),
        tip: 'Delete row',
        handle: (table, cells) => {
            table.removeRow(cells);
            table.hideTableTools();
        },
    },
    {
        name: 'DeleteColumn',
        icon: tableMenuIcon('−C'),
        tip: 'Delete column',
        handle: (table, cells) => {
            table.removeCol(cells);
            table.hideTableTools();
        },
    },
    {
        name: 'DeleteTable',
        icon: tableMenuIcon('×'),
        tip: 'Delete table',
        handle: (table, cells) => table.deleteTable(cells),
    },
];

const alignableBlockSelector = [
    'h1',
    'h2',
    'h3',
    'p',
    'blockquote',
    'li',
].join(',');

const tableCellGrid = (table) => {
    const grid = [];

    Array.from(table.rows).forEach((row, rowIndex) => {
        grid[rowIndex] ||= [];
        let columnIndex = 0;

        Array.from(row.cells).forEach((cell) => {
            while (grid[rowIndex][columnIndex]) columnIndex += 1;

            const rowSpan = Math.max(cell.rowSpan || 1, 1);
            const colSpan = Math.max(cell.colSpan || 1, 1);
            for (let rowOffset = 0; rowOffset < rowSpan; rowOffset += 1) {
                const targetRow = rowIndex + rowOffset;
                grid[targetRow] ||= [];
                for (let colOffset = 0; colOffset < colSpan; colOffset += 1) {
                    grid[targetRow][columnIndex + colOffset] = cell;
                }
            }

            columnIndex += colSpan;
        });
    });

    return grid;
};

const moveTableCaretVertically = (quill, context, direction) => {
    const currentCell = context.line?.domNode?.closest?.('td.ql-table-cell');
    const table = currentCell?.closest('table.ql-table');
    if (!currentCell || !table) return true;

    const rows = Array.from(table.rows);
    const currentRowIndex = rows.indexOf(currentCell.parentElement);
    const grid = tableCellGrid(table);
    const currentColumnIndex = grid[currentRowIndex]?.indexOf(currentCell) ?? -1;
    if (currentRowIndex < 0 || currentColumnIndex < 0) return true;

    const targetRowIndex = direction > 0
        ? currentRowIndex + Math.max(currentCell.rowSpan || 1, 1)
        : currentRowIndex - 1;
    const targetCell = grid[targetRowIndex]?.[currentColumnIndex];
    if (!targetCell || targetCell === currentCell) return true;

    const targetLineNode = targetCell.querySelector('.ql-table-cell-inner')?.firstElementChild;
    const targetLine = targetLineNode ? Quill.find(targetLineNode) : null;
    if (!targetLine || typeof targetLine.length !== 'function') return true;

    const offset = Math.min(context.offset, Math.max(targetLine.length() - 1, 0));
    quill.setSelection(quill.getIndex(targetLine) + offset, 0, Quill.sources.USER);
    return false;
};

const registerTableArrowNavigation = (quill) => {
    const keyboard = quill.getModule('keyboard');

    [
        ['ArrowUp', -1],
        ['ArrowDown', 1],
    ].forEach(([key, direction]) => {
        keyboard.bindings[key] ||= [];
        keyboard.bindings[key].unshift({
            key,
            collapsed: true,
            shiftKey: false,
            altKey: false,
            ctrlKey: false,
            metaKey: false,
            handler(_range, context) {
                return moveTableCaretVertically(quill, context, direction);
            },
        });
    });
};

export function normalizeEditorHtml(html) {
    const document = new DOMParser().parseFromString(html || '', 'text/html');
    const textWalker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);

    while (textWalker.nextNode()) {
        textWalker.currentNode.nodeValue = textWalker.currentNode.nodeValue.replaceAll('\u00a0', ' ');
    }

    const blocks = Array.from(document.body.querySelectorAll(alignableBlockSelector))
        .filter((block) => !block.closest('.ql-table-wrapper'));
    const contentBlocks = blocks.filter((block) => block.textContent.trim() !== '');
    const hasGlobalJustifyArtifact = contentBlocks.length > 1
        && contentBlocks.every((block) => block.classList.contains('ql-align-justify'));

    if (hasGlobalJustifyArtifact) {
        blocks.forEach((block) => {
            block.classList.remove('ql-align-justify');
            if (block.classList.length === 0) block.removeAttribute('class');
        });
    }

    return document.body.innerHTML;
}

export function prepareEditorHtml(html) {
    const document = new DOMParser().parseFromString(html || '', 'text/html');

    document.body.querySelectorAll('.ql-table-wrapper table').forEach((table) => {
        const colgroup = table.querySelector('colgroup');
        const columns = Array.from(colgroup?.querySelectorAll('col') || []);
        const hasPercentageWidths = columns.length > 0
            && columns.every((column) => /^\d+(?:\.\d+)?%$/.test(column.getAttribute('width')?.trim() || ''));

        if (!hasPercentageWidths) return;

        table.dataset.full = 'true';
        colgroup.dataset.full = 'true';
        columns.forEach((column) => {
            column.dataset.full = 'true';
        });
    });

    return document.body.innerHTML;
}

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
                        ['blockquote', 'link', 'image', { [TableUp.toolName]: [] }],
                        ['clean'],
                    ],
                    handlers: {
                        align: alignHandler,
                        image: imageHandler,
                        list: listHandler,
                    },
                },
                [TableUp.moduleName]: {
                    customSelect: defaultCustomSelect,
                    customBtn: true,
                    full: true,
                    fullSwitch: false,
                    texts: tableTexts,
                    resize: SpreadsheetTableResize,
                    resizeScale: TableResizeScale,
                    resizeScaleOptions: {
                        blockSize: 14,
                    },
                    selection: TableSelection,
                    selectionOptions: {
                        selectColor: 'rgba(164, 126, 79, .14)',
                        tableMenu: TableMenuContextmenu,
                        tableMenuOptions: {
                            tools: tableMenuItems,
                            tipText: true,
                        },
                    },
                },
            },
        });
        registerTableArrowNavigation(quill);
        const initialContent = quill.clipboard.convert({ html: prepareEditorHtml(input.value) });
        quill.setContents(initialContent, Quill.sources.SILENT);
        const syncInput = () => {
            input.value = normalizeEditorHtml(quill.getSemanticHTML());
        };
        quill.on('text-change', syncInput);

        function alignHandler(value) {
            const tableModule = quill.getModule(TableUp.moduleName);
            const tableSelection = tableModule?.tableSelection;
            const selectedCells = tableSelection?.isDisplaySelection
                ? [...tableSelection.selectedTds]
                : [];

            if (selectedCells.length === 0) {
                quill.format('align', value || false, Quill.sources.USER);
                return;
            }

            const alignment = value || false;
            selectedCells.forEach((cell) => {
                const index = cell.offset(quill.scroll);
                const length = Math.max(cell.length() - 1, 1);
                quill.formatLine(index, length, 'align', alignment, Quill.sources.USER);
            });

            const connectedCells = selectedCells.filter((cell) => cell.domNode?.isConnected);
            if (connectedCells.length > 0) {
                tableSelection.selectedTds = connectedCells;
                tableSelection.updateWithSelectedTds();
            }
            syncInput();
        }

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
