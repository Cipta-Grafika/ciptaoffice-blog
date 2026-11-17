<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Menyimpan dan mengelola konfigurasi konten utama halaman beranda.
 */
class HomepageSetting extends Model
{
    protected $guarded = [];

    /**
     * Mengambil pengaturan beranda aktif dari cache atribut mentah dan merekonstruksi instance model.
     * Disimpan sebagai array agar kompatibel dengan cache.serializable_classes = false.
     *
     * @return self Instance pengaturan beranda yang aktif.
     */
    public static function current(): self
    {
        $attributes = Cache::remember('homepage.settings', now()->addHour(), function (): array {
            $record = static::query()->firstOrCreate([], [
                'eyebrow' => 'Solusi kebutuhan kantor',
                'title' => 'Ruang kerja yang siap mendukung setiap keputusan besar.',
                'summary' => 'CiptaOffice membantu perusahaan memenuhi kebutuhan meja, kursi, brankas, dan perlengkapan kantor dengan rekomendasi alternatif berkualitas setara.',
                'primary_cta_label' => 'Jelajahi pilihan',
                'primary_cta_url' => '#produk',
            ]);
            return $record->getAttributes();
        });
        $instance = new static();
        $instance->setRawAttributes($attributes, true);
        $instance->exists = true;
        return $instance;
    }

    /**
     * Mendaftarkan event lifecycle model untuk membersihkan cache beranda saat pengaturan diperbarui atau dihapus.
     */
    protected static function booted(): void
    {
        $flushCache = static function (): void {
            Cache::forget('homepage.settings');
            Cache::forget('home.sections');
        };

        static::saved($flushCache);
        static::deleted($flushCache);
    }
}
