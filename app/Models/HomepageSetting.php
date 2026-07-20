<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSetting extends Model
{
    protected $guarded = [];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'eyebrow' => 'Solusi kebutuhan kantor',
            'title' => 'Ruang kerja yang siap mendukung setiap keputusan besar.',
            'summary' => 'CiptaOffice membantu perusahaan memenuhi kebutuhan meja, kursi, brankas, dan perlengkapan kantor dengan rekomendasi alternatif berkualitas setara.',
            'primary_cta_label' => 'Jelajahi pilihan',
            'primary_cta_url' => '#produk',
        ]);
    }
}
