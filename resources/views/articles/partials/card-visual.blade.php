<div class="card-visual">
    @if ($post->cover_image_path)
        <img src="{{ asset('storage/' . $post->cover_image_path) }}"
            alt="{{ $post->cover_image_alt }}">
    @else
        <i class="bi bi-journal-richtext" aria-hidden="true"></i>
    @endif
</div>
