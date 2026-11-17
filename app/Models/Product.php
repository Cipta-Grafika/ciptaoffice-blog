<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

/**
 * Merepresentasikan item produk kantor beserta kategori dan galeri gambarnya.
 */
class Product extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['specifications' => 'array', 'is_featured' => 'boolean', 'is_active' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Mendaftarkan event lifecycle model untuk membersihkan cache sitemap, beranda, dan daftar produk kontak.
     */
    protected static function booted(): void
    {
        $flushCache = static function (): void {
            Cache::forget('sitemap.xml');
            Cache::forget('home.sections');
            Cache::forget('contact.products');
        };

        static::saved($flushCache);
        static::deleted($flushCache);
        static::restored($flushCache);
    }
}
