<?php

use App\Models\PostMedia;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Schedule::call(function (): void {
    PostMedia::query()->where('created_at', '<', now()->subDay())->with('post')->each(function (PostMedia $media): void {
        $url = Storage::disk('public')->url($media->path);
        if (! str_contains($media->post?->body_html ?? '', $url)) {
            $media->delete();
        }
    });
})->daily()->name('cleanup-orphaned-post-media')->withoutOverlapping();
