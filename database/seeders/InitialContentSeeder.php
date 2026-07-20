<?php

namespace Database\Seeders;

use App\Models\HomepageSetting;
use Illuminate\Database\Seeder;

class InitialContentSeeder extends Seeder
{
    public function run(): void
    {
        HomepageSetting::query()->updateOrCreate(['id' => 1], [
            'eyebrow' => 'Solusi kebutuhan kantor',
            'title' => 'Ruang kerja yang siap mendukung setiap keputusan besar.',
            'summary' => 'CiptaOffice membantu perusahaan memenuhi kebutuhan meja, kursi, brankas, dan perlengkapan kantor. Saat pilihan utama tidak tersedia, kami mencarikan alternatif dengan mutu yang tetap setara.',
            'primary_cta_label' => 'Jelajahi produk',
            'primary_cta_url' => '#produk',
            'secondary_cta_label' => 'Konsultasikan kebutuhan',
            'secondary_cta_url' => '/contact',
        ]);
    }
}
