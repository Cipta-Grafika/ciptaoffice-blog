<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Merepresentasikan ulasan/testimoni pelanggan yang ditampilkan di beranda.
 */
class Testimonial extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'rating' => 'integer'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Mendaftarkan event lifecycle model untuk membersihkan cache beranda saat testimoni diubah atau dihapus.
     */
    protected static function booted(): void
    {
        static::saved(fn() => Cache::forget('home.sections'));
        static::deleted(fn() => Cache::forget('home.sections'));
    }
}
