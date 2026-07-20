<?php

namespace Database\Seeders;

use App\Enums\PostStatus;
use App\Enums\UserRole;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => env('CMS_ADMIN_EMAIL', 'admin@ciptaoffice.test')],
            ['name' => env('CMS_ADMIN_NAME', 'Administrator CiptaOffice'), 'password' => Hash::make(env('CMS_ADMIN_PASSWORD') ?: 'password'), 'role' => UserRole::Admin, 'is_active' => true, 'email_verified_at' => now()]
        );

        foreach ([
            ['Merancang ruang kerja yang tetap relevan', 'Pilihan furnitur kantor seharusnya mengikuti cara tim bekerja, bukan sekadar memenuhi ruang.', '<h2>Mulai dari cara tim bekerja</h2><p>Ruang kerja yang baik menyeimbangkan fokus, kolaborasi, dan kenyamanan. Prioritaskan ukuran ruang, durasi penggunaan, dan fleksibilitas sebelum memilih bentuk furnitur.</p><h3>Kualitas yang terasa setiap hari</h3><p>Material, ergonomi, dan layanan purnajual memberi dampak lebih panjang dibanding keputusan yang hanya mengejar tampilan.</p>'],
            ['Memilih kursi kantor untuk penggunaan panjang', 'Ergonomi bukan aksesori; ia adalah investasi pada konsistensi dan kesehatan tim.', '<h2>Tiga penopang utama</h2><p>Perhatikan dukungan lumbar, rentang pengaturan, serta material dudukan. Kursi terbaik adalah yang dapat menyesuaikan diri pada pengguna.</p>'],
            ['Brankas kantor: sesuaikan proteksi dengan risiko', 'Kenali kebutuhan dokumen, lokasi penempatan, dan tingkat perlindungan sebelum memilih brankas.', '<h2>Proteksi yang proporsional</h2><p>Ukuran bukan satu-satunya ukuran keamanan. Mekanisme kunci, konstruksi, ketahanan api, dan prosedur akses perlu dinilai bersama.</p>'],
        ] as $index => [$title, $excerpt, $body]) {
            Post::query()->updateOrCreate(['slug' => Str::slug($title)], [
                'author_id' => $admin->id, 'title' => $title, 'excerpt' => $excerpt, 'body_html' => $body,
                'status' => PostStatus::Published, 'published_at' => now()->subDays($index + 1),
            ]);
        }

        foreach ([
            ['Rina Pratama', 'Office Manager', 'Perusahaan Distribusi', 'Tim CiptaOffice membantu kami menemukan alternatif kursi dengan spesifikasi setara saat pilihan awal tidak tersedia. Prosesnya jelas dan responsif.'],
            ['Fajar Nugroho', 'Procurement Lead', 'Perusahaan Jasa', 'Rekomendasinya tidak sekadar menjual produk, tetapi mempertimbangkan ukuran ruang dan kebutuhan penggunaan harian.'],
            ['Dewi Kartika', 'General Affairs', 'Perusahaan Teknologi', 'Komunikasi rapi, pilihan produk relevan, dan pengadaan perlengkapan kantor menjadi jauh lebih terarah.'],
        ] as $index => [$name, $title, $company, $quote]) {
            Testimonial::query()->updateOrCreate(['reviewer_name' => $name], [
                'reviewer_title' => $title, 'company' => $company, 'quote' => $quote, 'rating' => 5,
                'is_active' => true, 'sort_order' => $index + 1,
            ]);
        }

        $categories = [
            ['Meja Kantor', 'meja-kantor', 'Meja kerja individual, kolaboratif, dan eksekutif untuk berbagai skala ruang.'],
            ['Kursi Kantor', 'kursi-kantor', 'Pilihan kursi ergonomis untuk penggunaan harian yang nyaman dan konsisten.'],
            ['Brankas', 'brankas', 'Penyimpanan dokumen dan aset penting dengan tingkat proteksi yang sesuai kebutuhan.'],
        ];

        foreach ($categories as $order => [$name, $slug, $description]) {
            $category = ProductCategory::query()->updateOrCreate(['slug' => $slug], ['name' => $name, 'description' => $description, 'sort_order' => $order + 1, 'is_active' => true]);
            Product::query()->updateOrCreate(['slug' => $slug.'-signature'], [
                'product_category_id' => $category->id, 'name' => $name.' Signature',
                'summary' => $description, 'description' => 'Produk contoh untuk menampilkan struktur katalog. Hubungi tim kami untuk spesifikasi, pilihan material, dan alternatif yang tersedia.',
                'specifications' => ['Layanan' => 'Konsultasi kebutuhan', 'Ketersediaan' => 'Konfirmasi melalui tim CiptaOffice'],
                'is_featured' => true, 'is_active' => true, 'sort_order' => $order + 1,
            ]);
        }
    }
}
