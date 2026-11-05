<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan file upload lama sebelum seeding data baru
        Storage::disk('public')->deleteDirectory('articles');
        Storage::disk('public')->deleteDirectory('homepage');
        Storage::disk('public')->deleteDirectory('products');
        Storage::disk('public')->deleteDirectory('testimonials');

        $this->call([InitialContentSeeder::class, AdminUserSeeder::class]);

        if (app()->environment(['local', 'testing'])) {
            $this->call(DemoContentSeeder::class);
        }
    }
}
