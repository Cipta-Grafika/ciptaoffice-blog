<?php

use App\Models\PostMedia;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Schedule::call(function (): void {
    /** @var FilesystemAdapter $disk */
    $disk = Storage::disk('public');

    /** @var EloquentCollection<int, PostMedia> $mediaCandidates */
    $mediaCandidates = PostMedia::query()
        ->where('created_at', '<', now()->subDay())
        ->with('post')
        ->get();

    $mediaCandidates->each(function (PostMedia $media) use ($disk): void {
        $url = $disk->url($media->path);

        if (! str_contains($media->post?->body_html ?? '', $url)) {
            $media->delete();
        }
    });

    PostMedia::pruneEmptyArticleDirectories();
})->daily()->name('cleanup-orphaned-post-media')->withoutOverlapping();
