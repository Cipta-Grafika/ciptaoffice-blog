<x-cms-table kicker="Kutipan pelanggan" record-label="testimonial" :paginator="$testimonials" :column-count="5" :empty="$testimonials->isEmpty()" empty-icon="chat-quote"
    empty-title="Belum ada testimonial" empty-description="Tambahkan pengalaman pelanggan pertama.">
    <x-slot:filters>
        <div class="d-flex align-items-center gap-2">
            <form class="cms-filter-form mb-0" method="get">
                <select class="form-select form-select-sm" name="status" aria-label="Filter status">
                    <option value="">Semua status</option>
                    <option value="1" @selected(request('status') === '1')>Aktif</option>
                    <option value="0" @selected(request('status') === '0')>Nonaktif</option>
                </select>
            </form>
            <a class="btn btn-sm btn-outline-secondary" href="#">
                <i class="bi bi-upload" aria-hidden="true"></i><span class="cms-action-label--compact ms-1">Import</span>
            </a>
            <a class="btn btn-sm btn-primary" href="{{ route('cms.testimonials.create') }}">
                <i class="bi bi-plus-lg" aria-hidden="true"></i><span class="cms-action-label--compact ms-1">Create</span>
            </a>
        </div>
    </x-slot:filters>
    <x-slot:head>
        <th>Nama</th>
        <th>Perusahaan</th>
        <th>Status</th>
        <th>Urutan</th>
        <th class="text-end">Tindakan</th>
    </x-slot:head>
    @foreach ($testimonials as $item)
        <tr>
            <td><span class="cms-table-primary">{{ $item->reviewer_name }}</span><span class="cms-table-secondary">{{ Str::limit($item->quote, 90) }}</span></td>
            <td>{{ $item->company ?? '—' }}</td>
            <td><span class="cms-status {{ $item->is_active ? 'cms-status--success' : '' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
            <td>{{ $item->sort_order }}</td>
            <td><div class="cms-table-actions"><a class="btn btn-sm btn-outline-primary" href="{{ route('cms.testimonials.edit', $item) }}"><i class="bi bi-pencil-square" aria-hidden="true"></i>Edit</a></div></td>
        </tr>
    @endforeach
</x-cms-table>
