@extends('layouts.cms')

@section('title', 'Testimonial')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Kepercayaan" title="Testimonial"
            description="Kelola kutipan pelanggan yang memperkuat reputasi CiptaOffice di homepage.">
            <x-slot:actions><a class="btn btn-primary" href="{{ route('cms.testimonials.create') }}"><i class="bi bi-plus-lg"></i><span class="cms-action-label--compact">Tambah testimonial</span></a></x-slot:actions>
        </x-cms-page-header>
        <section class="cms-surface" aria-label="Daftar testimonial">
            <div class="cms-list-toolbar"><p class="cms-surface-kicker mb-0">Kutipan pelanggan</p><p class="cms-record-count mb-0">{{ $testimonials->total() }} testimonial</p></div>
            <div class="table-responsive">
                <table class="table table-hover cms-table mb-0">
                    <thead><tr><th>Nama</th><th>Perusahaan</th><th>Status</th><th>Urutan</th><th class="text-end">Tindakan</th></tr></thead>
                    <tbody>
                        @forelse($testimonials as $item)
                            <tr>
                                <td><span class="cms-table-primary">{{ $item->reviewer_name }}</span><span class="cms-table-secondary">{{ Str::limit($item->quote, 90) }}</span></td>
                                <td>{{ $item->company ?? '—' }}</td>
                                <td><span class="cms-status {{ $item->is_active ? 'cms-status--success' : '' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td>{{ $item->sort_order }}</td>
                                <td><div class="cms-table-actions"><a class="btn btn-sm btn-outline-primary" href="{{ route('cms.testimonials.edit', $item) }}">Edit</a></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="cms-empty-state"><span class="cms-empty-state-icon"><i class="bi bi-chat-quote"></i></span><h2>Belum ada testimonial</h2><p class="mb-0">Tambahkan pengalaman pelanggan pertama.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <div class="cms-pagination">{{ $testimonials->links() }}</div>
    </div>
@endsection
