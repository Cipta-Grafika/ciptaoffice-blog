<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaUploadController extends Controller
{
    public function store(Request $request, Post $post): JsonResponse
    {
        $this->authorize('update', $post);
        $data = $request->validate(['image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'], 'alt_text' => ['required', 'string', 'max:180']]);
        $path = $request->file('image')->store('articles/'.$post->id.'/inline', 'public');
        $media = $post->media()->create(['uploaded_by' => $request->user()->id, 'path' => $path, 'alt_text' => $data['alt_text']]);

        return response()->json(['url' => Storage::disk('public')->url($media->path), 'alt' => $media->alt_text]);
    }
}
