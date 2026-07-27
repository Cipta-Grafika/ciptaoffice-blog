@extends('layouts.cms')

@section('title', 'Kategori produk')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Struktur katalog" title="Kategori"
            description="Susun kelompok produk agar katalog tetap mudah dijelajahi pelanggan.">
            <x-slot:actions>
                <a class="btn btn-primary" href="{{ route('cms.categories.create') }}">
                    <i class="bi bi-plus-lg"></i>
                    <span class="cms-action-label--compact">Tambah kategori</span>
                </a>
            </x-slot:actions>
        </x-cms-page-header>
        <section class="cms-surface" aria-label="Daftar kategori produk">
            <x-cms-table kicker="Taksonomi produk" record-label="kategori" :paginator="$categories" :column-count="5"
                :empty="$categories->isEmpty()" empty-icon="tags" empty-title="Belum ada kategori"
                empty-description="Buat kategori untuk mulai menyusun katalog.">
                <x-slot:head>
                    <th>Kategori</th>
                    <th>Produk</th>
                    <th>Status</th>
                    <th>Urutan</th>
                    <th class="text-end">Tindakan</th>
                </x-slot:head>
                @foreach ($categories as $category)
                    <tr>
                        <td><span class="cms-table-primary">{{ $category->name }}</span><span
                                class="cms-table-secondary">/{{ $category->slug }}</span></td>
                        <td>{{ $category->products_count }}</td>
                        <td><span
                                class="cms-status {{ $category->is_active ? 'cms-status--success' : '' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </td>
                        <td>{{ $category->sort_order }}</td>
                        <td>
                            <div class="cms-table-actions"><a class="btn btn-sm btn-outline-primary"
                                    href="{{ route('cms.categories.edit', $category) }}"><i class="bi bi-pencil-square"
                                        aria-hidden="true"></i>Edit</a></div>
                        </td>
                    </tr>
                @endforeach
            </x-cms-table>
        </section>
    </div>
@endsection
