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
            <x-cms-table kicker="Daftar artikel" record-label="artikel" :paginator="$posts"
                :column-count="auth()->user()->can('admin') ? 5 : 4" :empty="$posts->isEmpty()"
                empty-icon="file-earmark-text" empty-title="Belum ada artikel"
                empty-description="Mulai alur editorial dengan membuat draft pertama.">
                <x-slot:filters>
                    <form class="cms-filter-form" method="get">
                        <select class="form-select form-select-sm" name="status" aria-label="Filter status artikel">
                            <option value="">Semua status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-outline-secondary" type="submit">
                            <i class="bi bi-funnel" aria-hidden="true"></i> Filter
                        </button>
                    </form>
                </x-slot:filters>
                <x-slot:head>
                    <th>Artikel</th>
                    @can('admin')
                        <th>Author</th>
                    @endcan
                    <th>Status</th>
                    <th>Terakhir diubah</th>
                    <th class="text-end">Tindakan</th>
                </x-slot:head>
                @foreach ($posts as $post)
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
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('cms.posts.edit', $post) }}"><i
                                            class="bi bi-pencil-square" aria-hidden="true"></i>Edit</a>
                                @elsecan('admin')
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('cms.posts.edit', $post) }}"><i
                                            class="bi bi-eye" aria-hidden="true"></i>Tinjau</a>
                                @endcan
                                @if ($post->status === \App\Enums\PostStatus::Published)
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('articles.show', $post) }}"
                                        target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-right"
                                            aria-hidden="true"></i>Lihat</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-cms-table>
        </section>
    </div>
@endsection
