@extends('layouts.cms')

@section('title', 'Artikel')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Editorial" title="Artikel"
            description="Kelola draft, proses review, dan publikasi artikel CiptaOffice.">
            <x-slot:actions>
                <a class="btn btn-primary" href="{{ route('cms.posts.create') }}">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i><span class="cms-action-label--compact">Artikel baru</span>
                </a>
            </x-slot:actions>
        </x-cms-page-header>

        <section class="cms-surface" aria-label="Daftar artikel">
            <div class="cms-list-toolbar">
                <form class="cms-filter-form" method="get">
                    <select class="form-select" name="status" aria-label="Filter status artikel">
                        <option value="">Semua status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i>
                        Filter</button>
                </form>
                <p class="cms-record-count mb-0">{{ $posts->total() }} artikel ditemukan</p>
            </div>
            <div class="table-responsive">
                <table class="table table-hover cms-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Artikel</th>
                            @can('admin')
                                <th>Author</th>
                            @endcan
                            <th>Status</th>
                            <th>Terakhir diubah</th>
                            <th class="text-end">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $post)
                            <tr>
                                <td><span class="cms-table-primary">{{ $post->title }}</span><span
                                        class="cms-table-secondary">/{{ $post->slug }}</span></td>
                                @can('admin')
                                    <td>{{ $post->author?->name ?? 'Konten impor' }}</td>
                                @endcan
                                <td><span
                                        class="cms-status cms-status--{{ $post->status->badge() }}">{{ $post->status->label() }}</span>
                                </td>
                                <td>{{ $post->updated_at->diffForHumans() }}</td>
                                <td>
                                    <div class="cms-table-actions">
                                        @can('update', $post)
                                            <a class="btn btn-sm btn-outline-primary"
                                                href="{{ route('cms.posts.edit', $post) }}">Edit</a>
                                        @elsecan('admin')
                                            <a class="btn btn-sm btn-outline-primary"
                                                href="{{ route('cms.posts.edit', $post) }}">Tinjau</a>
                                        @endcan
                                        @if ($post->status === \App\Enums\PostStatus::Published)
                                            <a class="btn btn-sm btn-outline-secondary"
                                                href="{{ route('articles.show', $post) }}" target="_blank"
                                                rel="noopener noreferrer">Lihat</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="cms-empty-state"><span class="cms-empty-state-icon"><i
                                                class="bi bi-file-earmark-text"></i></span>
                                        <h2>Belum ada artikel</h2>
                                        <p class="mb-0">Mulai alur editorial dengan membuat draft pertama.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <div class="cms-pagination">{{ $posts->links() }}</div>
    </div>
@endsection
