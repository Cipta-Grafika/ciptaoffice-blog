<?php

namespace App\Http\Controllers\Cms;

use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Services\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Post::class);
        $query = Post::with('author')->latest();
        if (! $request->user()->isAdmin()) {
            $query->where('author_id', $request->user()->id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('cms.posts.index', ['posts' => $query->paginate(15)->withQueryString(), 'statuses' => PostStatus::cases()]);
    }

    public function create(): View
    {
        $this->authorize('create', Post::class);

        return view('cms.posts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Post::class);
        $data = $request->validate(['title' => ['required', 'string', 'max:180']]);
        $slug = $this->uniqueSlug($data['title']);
        $post = Post::create(['author_id' => $request->user()->id, 'title' => $data['title'], 'slug' => $slug, 'status' => PostStatus::Draft]);

        return redirect()->route('cms.posts.edit', $post)->with('success', 'Draft dibuat. Lengkapi isi artikel.');
    }

    public function edit(Post $post): View
    {
        $this->authorize('update', $post);

        return view('cms.posts.edit', compact('post'));
    }

    public function update(UpdatePostRequest $request, Post $post, HtmlSanitizer $sanitizer): RedirectResponse
    {
        $data = $request->safe()->except('cover_image');
        $data['body_html'] = $sanitizer->clean($data['body_html']);
        if ($request->hasFile('cover_image')) {
            if ($post->cover_image_path) {
                Storage::disk('public')->delete($post->cover_image_path);
            } $data['cover_image_path'] = $request->file('cover_image')->store('articles/covers', 'public');
        }
        if ($request->hasFile('cover_image') || $post->cover_image_path) {
            $data['cover_image_alt'] = $data['title'];
        }
        $post->update($data);
        $this->cleanUnusedMedia($post);

        return back()->with('success', 'Artikel berhasil disimpan.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);
        $post->delete();

        return redirect()->route('cms.posts.index')->with('success', 'Artikel dipindahkan ke sampah.');
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: Str::random(8);
        $slug = $base;
        $i = 2;
        while (Post::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

return $slug;
    }

    private function cleanUnusedMedia(Post $post): void
    {
        $post->media()->get()->each(function ($media) use ($post) {
            if (! str_contains($post->body_html ?? '', Storage::disk('public')->url($media->path))) {
                $media->delete();
            }
        });
    }
}
