<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaUploadController extends Controller
{
    public function store(Request $request, Post $post): JsonResponse
    {
        $this->authorize('update', $post);
        $request->validate(['image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096']]);
        $image = $request->file('image');
        $filename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $altText = Str::limit(Str::headline($filename), 180, '') ?: Str::limit($post->title, 180, '');
        $path = $image->store('articles/'.$post->id.'/inline', 'public');
        $media = $post->media()->create([
            'uploaded_by' => $request->user()->id, 
            'path' => $path, 
            'alt_text' => $altText
        ]);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        return response()->json([
            'url' => $disk->url($media->path),
            'alt' => $media->alt_text
        ]);
    }
}
