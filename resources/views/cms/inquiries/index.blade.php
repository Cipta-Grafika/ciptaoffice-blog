@extends('layouts.cms')

@section('title', 'Inquiry')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Permintaan pelanggan" title="Inquiry"
            description="Pantau kebutuhan yang masuk dan pastikan setiap calon pelanggan mendapat tindak lanjut." />
        <section class="cms-surface" aria-label="Daftar inquiry">
            <div class="cms-list-toolbar"><p class="cms-surface-kicker mb-0">Kotak masuk</p><p class="cms-record-count mb-0">{{ $inquiries->total() }} inquiry</p></div>
            <x-cms-table :paginator="$inquiries" :column-count="5" :empty="$inquiries->isEmpty()" empty-icon="inbox"
                empty-title="Belum ada inquiry" empty-description="Permintaan pelanggan baru akan muncul di sini.">
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
        </section>
    </div>
@endsection
