@extends('layouts.cms')

@section('title', 'Testimonial')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Kepercayaan" title="Testimonial"
            description="Kelola kutipan pelanggan yang memperkuat reputasi CiptaOffice di homepage.">
            <x-slot:actions><a class="btn btn-primary" href="{{ route('cms.testimonials.create') }}"><i class="bi bi-plus-lg"></i><span class="cms-action-label--compact">Tambah testimonial</span></a></x-slot:actions>
        </x-cms-page-header>
        <section class="cms-surface" aria-label="Daftar testimonial">
            <x-cms-table kicker="Kutipan pelanggan" record-label="testimonial" :paginator="$testimonials" :column-count="5" :empty="$testimonials->isEmpty()" empty-icon="chat-quote"
                empty-title="Belum ada testimonial" empty-description="Tambahkan pengalaman pelanggan pertama.">
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
        </section>
    </div>
@endsection
