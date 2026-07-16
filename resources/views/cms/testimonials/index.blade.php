@extends('layouts.cms')
@section('title', 'Testimonial')
@section('content')<div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <p class="section-kicker mb-1">Kepercayaan</p>
            <h1 class="font-display display-5 mb-0">Testimonial</h1>
        </div><a class="btn btn-primary" href="{{ route('cms.testimonials.create') }}">Tambah testimonial</a>
    </div>
    <div class="cms-card table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Perusahaan</th>
                    <th>Status</th>
                    <th>Urutan</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($testimonials as $item)
                    <tr>
                        <td><strong>{{ $item->reviewer_name }}</strong><small
                                class="d-block text-muted">{{ Str::limit($item->quote, 70) }}</small></td>
                        <td>{{ $item->company ?? '—' }}</td>
                        <td><span
                                class="badge text-bg-{{ $item->is_active ? 'success' : 'secondary' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </td>
                        <td>{{ $item->sort_order }}</td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-primary"
                                href="{{ route('cms.testimonials.edit', $item) }}">Edit</a></td>
                </tr>@empty<tr>
                        <td colspan="5" class="text-center py-5">Belum ada testimonial.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $testimonials->links() }}</div>
@endsection
