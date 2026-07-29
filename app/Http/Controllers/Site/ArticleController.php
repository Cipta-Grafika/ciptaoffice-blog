<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $posts = Post::published()
            ->with('author')
            ->when($q, fn ($query) => $query->where(fn ($sub) => $sub->where('title', 'like', '%'.$q.'%')->orWhere('excerpt', 'like', '%'.$q.'%')))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('articles.index', compact('posts', 'q'));
    }

    public function show(Post $post): View
    {
        abort_unless(Post::published()->whereKey($post->id)->exists(), 404);
        $post->load('author');
        $latest = Post::published()->whereKeyNot($post->id)->latest('published_at')->limit(3)->get();

        return view('articles.show', compact('post', 'latest'));
    }
}
