import { expect, test } from '@playwright/test';
import fs from 'node:fs';
import { fileURLToPath } from 'node:url';

const fixturePath = fileURLToPath(new URL('./fixtures/wordpress-posts.json', import.meta.url));

const tableSignature = (html) => {
    const document = new DOMParser().parseFromString(html, 'text/html');
    const table = document.querySelector('table');

    return {
        hasTable: Boolean(table),
        hasThead: Boolean(table?.querySelector('thead')),
        hasTbody: Boolean(table?.querySelector('tbody')),
        headerCells: table?.querySelectorAll('th').length ?? 0,
        dataCells: table?.querySelectorAll('td').length ?? 0,
        rowSpans: Array.from(table?.querySelectorAll('[rowspan]:not([rowspan="1"])') ?? [], (cell) => cell.getAttribute('rowspan')),
        colSpans: Array.from(table?.querySelectorAll('[colspan]:not([colspan="1"])') ?? [], (cell) => cell.getAttribute('colspan')),
    };
};

test('WordPress table survives import, Quill editing, save, and reopen', async ({ page }) => {
    const fixture = fs.readFileSync(fixturePath, 'utf8');
    expect(fixture).not.toContain('<colgroup');

    await page.goto('/cms/login');
    await page.getByLabel('Email').fill('browser-test@ciptaoffice.test');
    await page.getByLabel('Kata sandi', { exact: true }).fill('Browser-test-password');
    await page.getByRole('button', { name: 'Masuk CMS' }).click();
    await expect(page).toHaveURL(/\/cms$/);

    await page.goto('/cms/posts');
    await page.getByRole('button', { name: 'Import' }).click();
    await expect(page.getByRole('dialog', { name: 'Import artikel' })).toBeVisible();
    await page.locator('[data-cms-import-input]').setInputFiles(fixturePath);
    await expect(page.locator('[data-cms-import-filename]')).toHaveText('wordpress-posts.json');
    await page.getByRole('button', { name: 'Import artikel' }).click();
    await expect(page.getByText('1 artikel berhasil diimpor sebagai draft.')).toBeVisible();

    const articleRow = page.getByRole('row').filter({ hasText: 'Panduan Meja WordPress' });
    await articleRow.getByRole('link', { name: 'Edit' }).click();
    await expect(page.locator('.ql-editor table.ql-table').filter({ hasText: 'Meja Point' })).toBeVisible();

    const initialDatabaseHtml = await page.locator('#body_html').inputValue();
    const initialSignature = await page.evaluate(tableSignature, initialDatabaseHtml);
    expect(initialSignature).toEqual({
        hasTable: true,
        hasThead: true,
        hasTbody: true,
        headerCells: 4,
        dataCells: 4,
        rowSpans: ['2', '2'],
        colSpans: ['2', '2'],
    });

    const editorTable = page.locator('.ql-editor .ql-table-wrapper table.ql-table').filter({ hasText: 'Meja Point' });
    await expect(editorTable).toBeVisible();
    await expect(editorTable.locator('tbody')).toHaveCount(1);
    await expect(editorTable.locator('td[data-table-header="true"]')).toHaveCount(4);
    await expect(editorTable.locator('[rowspan="2"]')).toHaveCount(2);
    await expect(editorTable.locator('[colspan="2"]')).toHaveCount(2);

    const editableCell = editorTable.locator('td').filter({ hasText: '120 cm' });
    await editableCell.click();
    await page.keyboard.press('End');
    await page.keyboard.type(' diperbarui');
    await expect(editableCell).toContainText('120 cm diperbarui');

    await page.getByRole('button', { name: 'Simpan' }).click();
    await expect(page.getByText('Artikel berhasil disimpan.')).toBeVisible();
    await expect(page.locator('.ql-editor table.ql-table').filter({ hasText: 'Meja Point' })).toBeVisible();

    const savedDatabaseHtml = await page.locator('#body_html').inputValue();
    const savedSignature = await page.evaluate(tableSignature, savedDatabaseHtml);
    expect(savedSignature).toEqual(initialSignature);
    expect(savedDatabaseHtml).toContain('120 cm diperbarui');

    await page.reload();
    const reopenedTable = page.locator('.ql-editor .ql-table-wrapper table.ql-table').filter({ hasText: 'Meja Point' });
    await expect(reopenedTable).toBeVisible();
    await expect(reopenedTable).toContainText('120 cm diperbarui');
    await expect(reopenedTable.locator('tbody')).toHaveCount(1);
    await expect(reopenedTable.locator('td[data-table-header="true"]')).toHaveCount(4);
    await expect(reopenedTable.locator('[rowspan="2"]')).toHaveCount(2);
    await expect(reopenedTable.locator('[colspan="2"]')).toHaveCount(2);
});
