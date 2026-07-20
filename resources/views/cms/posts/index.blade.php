@extends('layouts.cms')
@section('title', 'Artikel')
@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="section-kicker mb-1">Editorial</p>
            <h1 class="font-display display-5 mb-0">Artikel</h1>
        </div>
        <a class="btn btn-primary" href="{{ route('cms.posts.create') }}"><i class="bi bi-plus-lg"></i> Artikel baru</a>
    </div>
    <form class="d-flex gap-2 mb-3" method="get"><select class="form-select" name="status" style="max-width:14rem">
            <option value="">Semua status</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-secondary">Filter</button>
    </form>
    <div class="cms-card table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Artikel</th>
                    @can('admin')
                        <th>Author</th>
                    @endcan
                    <th>
                        Status</th>
                    <th>Terakhir diubah</th>
                    <th class="text-end">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                    <tr>
                        <td>
                            <strong>{{ $post->title }}</strong>
                            <small class="d-block text-muted">/{{ $post->slug }}</small>
                        </td>
                        @can('admin')
                            <td>{{ $post->author?->name ?? 'Konten impor' }}</td>
                        @endcan
                        <td>
                            <span class="badge text-bg-{{ $post->status->badge() }}">{{ $post->status->label() }}</span>
                        </td>
                        <td>{{ $post->updated_at->diffForHumans() }}</td>
                        <td class="text-end">
                            @can('update', $post)
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('cms.posts.edit', $post) }}">Edit</a>
                            @else
                                @can('admin')
                                    <a class="btn btn-sm btn-outline-primary"
                                        href="{{ route('cms.posts.edit', $post) }}">Tinjau</a>
                                @endcan
                            @endcan
                            @if ($post->status === \App\Enums\PostStatus::Published)
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('articles.show', $post) }}"
                                    target="_blank">Lihat</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state border-0">
                                <h2 class="h4">Belum ada artikel</h2>
                                <p class="mb-0 text-muted">Mulai dengan membuat draft pertama.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $posts->links() }}</div>
@endsection
