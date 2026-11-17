<?php

namespace App\Models;

use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;


/**
 * Merepresentasikan artikel blog/editorial beserta alur status publikasi dan medianya.
 */
class Post extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['status' => PostStatus::class, 'submitted_at' => 'datetime', 'reviewed_at' => 'datetime', 'published_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getDisplayAuthorNameAttribute(): string
    {
        return $this->author_name ?: $this->author?->name ?: 'Tim CiptaOffice';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Published->value)->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * Mendaftarkan event lifecycle model untuk invalidasi cache sitemap/beranda dan pembersihan media saat hapus permanen.
     */
    protected static function booted(): void
    {
        $flushCache = static function (): void {
            Cache::forget('sitemap.xml');
            Cache::forget('home.sections');
        };

        static::saved($flushCache);
        static::deleted($flushCache);
        static::restored($flushCache);
        
        static::forceDeleting(function (Post $post): void {
            if ($post->cover_image_path) {
                Storage::disk('public')->delete($post->cover_image_path);
            }

            $post->media()->each(fn (PostMedia $media) => $media->delete());
        });
    }
}
