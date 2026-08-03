@if ($posts->isEmpty())
    <div class="empty-state">
        <h2>Tidak ada artikel ditemukan</h2>
        <p class="text-muted mb-0">Coba kata kunci lain atau lihat kembali seluruh artikel.</p>
    </div>
@else
    <div class="row g-4">
        @foreach ($posts as $post)
            <div class="col-md-6 col-lg-4">
                <article class="article-card">
                    @include('articles.partials.card-visual', ['post' => $post])
                    <div class="p-4">
                        <p class="article-meta">{{ $post->published_at->translatedFormat('d M Y') }}</p>
                        <h2 class="card-title card-text-clamp">
                            <a class="stretched-link text-dark text-decoration-none"
                                href="{{ route('articles.show', $post) }}">
                                {{ $post->title }}
                            </a>
                        </h2>
                        <p class="card-text-clamp text-muted mt-1">{{ $post->excerpt }}</p>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
    <div class="mt-5">{{ $posts->links() }}</div>
@endif
