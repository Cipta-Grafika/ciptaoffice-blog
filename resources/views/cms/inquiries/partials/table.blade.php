<x-cms-table kicker="Kotak masuk" record-label="inquiry" :paginator="$inquiries" :column-count="5" :empty="$inquiries->isEmpty()" empty-icon="inbox"
    empty-title="Belum ada inquiry" empty-description="Permintaan pelanggan baru akan muncul di sini.">
    <x-slot:filters>
        <form class="cms-filter-form" method="get">
            <select class="form-select form-select-sm" name="status" aria-label="Filter status">
                <option value="">Semua status</option>
                <option value="new" @selected(request('status') === 'new')>Baru</option>
                <option value="contacted" @selected(request('status') === 'contacted')>Dihubungi</option>
                <option value="closed" @selected(request('status') === 'closed')>Selesai</option>
            </select>
        </form>
    </x-slot:filters>
    <x-slot:head>
        <th>Kontak</th>
        <th>Produk</th>
        <th>Status</th>
        <th>Diterima</th>
        <th class="text-end">Tindakan</th>
    </x-slot:head>
    @foreach ($inquiries as $inquiry)
        @php($statusTone = ['new' => 'warning', 'contacted' => 'primary', 'closed' => 'success'][$inquiry->status] ?? '')
        <tr>
            <td><span class="cms-table-primary">{{ $inquiry->name }}</span><span class="cms-table-secondary">{{ $inquiry->phone }}</span></td>
            <td>{{ $inquiry->product?->name ?? 'Kebutuhan umum' }}</td>
            <td><span class="cms-status cms-status--{{ $statusTone }}">{{ ['new' => 'Baru', 'contacted' => 'Dihubungi', 'closed' => 'Selesai'][$inquiry->status] ?? $inquiry->status }}</span></td>
            <td>{{ $inquiry->created_at->translatedFormat('d M Y H:i') }}</td>
            <td><div class="cms-table-actions"><a class="btn btn-sm btn-outline-primary" href="{{ route('cms.inquiries.show', $inquiry) }}"><i class="bi bi-envelope-open" aria-hidden="true"></i>Buka</a></div></td>
        </tr>
    @endforeach
</x-cms-table>
