<?php

namespace App\Http\Controllers\Cms;

use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PostWorkflowController extends Controller
{
    public function submit(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('submit', $post);
        abort_unless($post->status->canSubmitForReview(), 422);

        if (blank($post->excerpt) || blank($post->body_html)) {
            throw ValidationException::withMessages(['article' => 'Ringkasan dan isi artikel wajib dilengkapi sebelum diajukan.']);
        }
        $post->update(['status' => PostStatus::PendingReview, 'submitted_at' => now(), 'review_note' => null]);

        return back()->with('success', 'Artikel diajukan untuk review.');
    }

    public function publish(Request $request, Post $post): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless(in_array($post->status, [PostStatus::PendingReview, PostStatus::Returned, PostStatus::Draft, PostStatus::Archived], true), 422);
        $post->update(['status' => PostStatus::Published, 'published_at' => now(), 'reviewed_at' => now(), 'reviewed_by' => $request->user()->id, 'review_note' => null]);

        return back()->with('success', 'Artikel diterbitkan.');
    }

    public function return(Request $request, Post $post): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($post->status === PostStatus::PendingReview, 422);
        $data = $request->validate(['review_note' => ['required', 'string', 'max:2000']]);
        $post->update(['status' => PostStatus::Returned, 'review_note' => $data['review_note'], 'reviewed_at' => now(), 'reviewed_by' => $request->user()->id]);

        return back()->with('success', 'Artikel dikembalikan kepada Author.');
    }

    public function archive(Request $request, Post $post): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($post->status === PostStatus::Published, 422);
        $post->update(['status' => PostStatus::Archived]);

        return back()->with('success', 'Artikel diarsipkan.');
    }
}
