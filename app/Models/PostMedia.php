<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PostMedia extends Model
{
    protected $table = 'post_media';

    protected $guarded = [];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    protected static function booted(): void
    {
        static::deleted(function (PostMedia $media): void {
            $disk = Storage::disk('public');
            $path = trim($media->path, '/');

            $disk->delete($path);

            if (! preg_match('#^articles/([^/]+)/inline/[^/]+$#', $path, $matches)) {
                return;
            }

            self::deleteDirectoryIfEmpty('articles/'.$matches[1].'/inline');
            self::deleteDirectoryIfEmpty('articles/'.$matches[1]);
        });
    }

    public static function pruneEmptyArticleDirectories(): void
    {
        $disk = Storage::disk('public');

        foreach ($disk->directories('articles') as $directory) {
            $articleId = basename($directory);

            if (ctype_digit($articleId)) {
                self::deleteDirectoryIfEmpty($directory);
            }
        }
    }

    private static function deleteDirectoryIfEmpty(string $directory): void
    {
        $disk = Storage::disk('public');

        if ($disk->allFiles($directory) === []) {
            $disk->deleteDirectory($directory);
        }
    }
}
