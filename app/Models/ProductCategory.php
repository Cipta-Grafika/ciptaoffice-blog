<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * Merepresentasikan kategori produk pada katalog CiptaOffice.
 */
class ProductCategory extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Mendaftarkan event lifecycle model untuk membersihkan cache beranda dan daftar kategori aktif.
     */
    protected static function booted(): void
    {
        $flushCache = static function (): void {
            Cache::forget('home.sections');
            Cache::forget('products.active_categories');
        };

        static::saved($flushCache);
        static::deleted($flushCache);
    }
}
