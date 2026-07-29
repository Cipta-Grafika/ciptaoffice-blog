<x-cms-table kicker="Taksonomi produk" record-label="kategori" :paginator="$categories" :column-count="5"
    :empty="$categories->isEmpty()" empty-icon="tags" empty-title="Belum ada kategori"
    empty-description="Buat kategori untuk mulai menyusun katalog.">
    <x-slot:filters>
        <form class="cms-filter-form" method="get">
            <select class="form-select form-select-sm" name="status" aria-label="Filter status">
                <option value="">Semua status</option>
                <option value="1" @selected(request('status') === '1')>Aktif</option>
                <option value="0" @selected(request('status') === '0')>Nonaktif</option>
            </select>
        </form>
    </x-slot:filters>
    <x-slot:head>
        <th>Kategori</th>
        <th>Produk</th>
        <th>Status</th>
        <th>Urutan</th>
        <th class="text-end">Tindakan</th>
    </x-slot:head>
    @foreach ($categories as $category)
        <tr>
            <td><span class="cms-table-primary">{{ $category->name }}</span><span
                    class="cms-table-secondary">/{{ $category->slug }}</span></td>
            <td>{{ $category->products_count }}</td>
            <td><span
                    class="cms-status {{ $category->is_active ? 'cms-status--success' : '' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span>
            </td>
            <td>{{ $category->sort_order }}</td>
            <td>
                <div class="cms-table-actions"><a class="btn btn-sm btn-outline-primary"
                        href="{{ route('cms.categories.edit', $category) }}"><i class="bi bi-pencil-square"
                            aria-hidden="true"></i>Edit</a></div>
            </td>
        </tr>
    @endforeach
</x-cms-table>
