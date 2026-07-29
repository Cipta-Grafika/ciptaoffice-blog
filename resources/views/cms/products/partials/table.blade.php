<x-cms-table kicker="Katalog produk" record-label="produk" :paginator="$products" :column-count="5" :empty="$products->isEmpty()" empty-icon="box-seam"
    empty-title="Belum ada produk" empty-description="Tambahkan produk pertama ke katalog.">
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
        <th>Produk</th>
        <th>Kategori</th>
        <th>Status</th>
        <th>Unggulan</th>
        <th class="text-end">Tindakan</th>
    </x-slot:head>
    @foreach ($products as $product)
        <tr>
            <td><span class="cms-table-primary">{{ $product->name }}</span><span class="cms-table-secondary">{{ Str::limit($product->summary, 90) }}</span></td>
            <td>{{ $product->category->name }}</td>
            <td><span class="cms-status {{ $product->is_active ? 'cms-status--success' : '' }}">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
            <td>{{ $product->is_featured ? 'Ya' : 'Tidak' }}</td>
            <td><div class="cms-table-actions"><a class="btn btn-sm btn-outline-primary" href="{{ route('cms.products.edit', $product) }}"><i class="bi bi-pencil-square" aria-hidden="true"></i>Edit</a></div></td>
        </tr>
    @endforeach
</x-cms-table>
