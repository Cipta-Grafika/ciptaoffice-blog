@extends('layouts.cms')

@section('title', 'Kategori produk')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Struktur katalog" title="Kategori"
            description="Susun kelompok produk agar katalog tetap mudah dijelajahi pelanggan.">
            <x-slot:actions><a class="btn btn-primary" href="{{ route('cms.categories.create') }}"><i class="bi bi-plus-lg"></i><span class="cms-action-label--compact">Tambah kategori</span></a></x-slot:actions>
        </x-cms-page-header>
        <section class="cms-surface" aria-label="Daftar kategori produk">
            <div class="cms-list-toolbar"><p class="cms-surface-kicker mb-0">Taksonomi produk</p><p class="cms-record-count mb-0">{{ $categories->count() }} kategori</p></div>
            <div class="table-responsive">
                <table class="table table-hover cms-table mb-0">
                    <thead><tr><th>Kategori</th><th>Produk</th><th>Status</th><th>Urutan</th><th class="text-end">Tindakan</th></tr></thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td><span class="cms-table-primary">{{ $category->name }}</span><span class="cms-table-secondary">/{{ $category->slug }}</span></td>
                                <td>{{ $category->products_count }}</td>
                                <td><span class="cms-status {{ $category->is_active ? 'cms-status--success' : '' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td>{{ $category->sort_order }}</td>
                                <td><div class="cms-table-actions"><a class="btn btn-sm btn-outline-primary" href="{{ route('cms.categories.edit', $category) }}">Edit</a></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="cms-empty-state"><span class="cms-empty-state-icon"><i class="bi bi-tags"></i></span><h2>Belum ada kategori</h2><p class="mb-0">Buat kategori untuk mulai menyusun katalog.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
