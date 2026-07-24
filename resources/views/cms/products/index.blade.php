@extends('layouts.cms')

@section('title', 'Produk')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Katalog" title="Produk"
            description="Atur informasi produk, visibilitas, dan pilihan unggulan pada katalog publik.">
            <x-slot:actions><a class="btn btn-primary" href="{{ route('cms.products.create') }}"><i class="bi bi-plus-lg"></i><span class="cms-action-label--compact">Tambah produk</span></a></x-slot:actions>
        </x-cms-page-header>
        <section class="cms-surface" aria-label="Daftar produk">
            <div class="cms-list-toolbar"><p class="cms-surface-kicker mb-0">Katalog produk</p><p class="cms-record-count mb-0">{{ $products->total() }} produk</p></div>
            <div class="table-responsive">
                <table class="table table-hover cms-table mb-0">
                    <thead><tr><th>Produk</th><th>Kategori</th><th>Status</th><th>Unggulan</th><th class="text-end">Tindakan</th></tr></thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td><span class="cms-table-primary">{{ $product->name }}</span><span class="cms-table-secondary">{{ Str::limit($product->summary, 90) }}</span></td>
                                <td>{{ $product->category->name }}</td>
                                <td><span class="cms-status {{ $product->is_active ? 'cms-status--success' : '' }}">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td>{{ $product->is_featured ? 'Ya' : 'Tidak' }}</td>
                                <td><div class="cms-table-actions"><a class="btn btn-sm btn-outline-primary" href="{{ route('cms.products.edit', $product) }}">Edit</a></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="cms-empty-state"><span class="cms-empty-state-icon"><i class="bi bi-box-seam"></i></span><h2>Belum ada produk</h2><p class="mb-0">Tambahkan produk pertama ke katalog.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <div class="cms-pagination">{{ $products->links() }}</div>
    </div>
@endsection
