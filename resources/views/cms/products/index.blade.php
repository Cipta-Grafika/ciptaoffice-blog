@extends('layouts.cms')
@section('title', 'Produk')
@section('content')<div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <p class="section-kicker mb-1">Katalog</p>
            <h1 class="font-display display-5 mb-0">Produk</h1>
        </div><a class="btn btn-primary" href="{{ route('cms.products.create') }}">Tambah produk</a>
    </div>
    <div class="cms-card table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td><strong>{{ $product->name }}</strong><small
                                class="d-block text-muted">{{ Str::limit($product->summary, 70) }}</small></td>
                        <td>{{ $product->category->name }}</td>
                        <td><span
                                class="badge text-bg-{{ $product->is_active ? 'success' : 'secondary' }}">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </td>
                        <td>{{ $product->is_featured ? 'Ya' : 'Tidak' }}</td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-primary"
                                href="{{ route('cms.products.edit', $product) }}">Edit</a></td>
                </tr>@empty<tr>
                        <td colspan="5" class="text-center py-5">Belum ada produk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
@endsection
