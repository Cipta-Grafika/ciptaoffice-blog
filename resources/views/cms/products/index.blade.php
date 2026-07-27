@extends('layouts.cms')

@section('title', 'Produk')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Katalog" title="Produk"
            description="Atur informasi produk, visibilitas, dan pilihan unggulan pada katalog publik.">
            <x-slot:actions><a class="btn btn-primary" href="{{ route('cms.products.create') }}"><i class="bi bi-plus-lg"></i><span class="cms-action-label--compact">Tambah produk</span></a></x-slot:actions>
        </x-cms-page-header>
        <section class="cms-surface" aria-label="Daftar produk">
            <x-cms-table kicker="Katalog produk" record-label="produk" :paginator="$products" :column-count="5" :empty="$products->isEmpty()" empty-icon="box-seam"
                empty-title="Belum ada produk" empty-description="Tambahkan produk pertama ke katalog.">
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
        </section>
    </div>
@endsection
