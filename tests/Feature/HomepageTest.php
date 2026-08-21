<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_only_three_latest_published_posts_and_active_testimonials(): void
    {
        foreach (range(1, 4) as $day) {
            Post::create(['title' => 'Artikel '.$day, 'slug' => 'artikel-'.$day, 'excerpt' => 'Ringkasan '.$day, 'body_html' => '<p>Isi</p>', 'status' => PostStatus::Published, 'published_at' => now()->subDays($day)]);
        }
        Post::create(['title' => 'Rahasia draft', 'slug' => 'rahasia-draft', 'status' => PostStatus::Draft]);
        Testimonial::create(['reviewer_name' => 'Aktif', 'quote' => 'Ulasan aktif', 'is_active' => true, 'sort_order' => 1]);
        Testimonial::create(['reviewer_name' => 'Nonaktif', 'quote' => 'Ulasan tersembunyi', 'is_active' => false, 'sort_order' => 2]);
        $this->get('/')->assertOk()->assertSee('Artikel 1')->assertSee('Artikel 2')->assertSee('Artikel 3')->assertDontSee('Artikel 4')->assertDontSee('Rahasia draft')->assertSee('Ulasan aktif')->assertDontSee('Ulasan tersembunyi');
    }

    public function test_homepage_has_clear_empty_states(): void
    {
        $this->get('/')->assertOk()->assertSee('Artikel pertama sedang disiapkan')->assertSee('Ulasan akan segera hadir');
    }

    public function test_homepage_renders_testimonial_identity_with_avatar_and_initial_fallback(): void
    {
        Testimonial::create([
            'reviewer_name' => 'Rina Pratama',
            'reviewer_title' => 'Office Manager',
            'company' => 'Perusahaan Distribusi',
            'quote' => 'Pelayanannya responsif.',
            'avatar_path' => 'testimonials/rina-pratama.jpg',
            'avatar_alt' => 'Profil Rina Pratama',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        Testimonial::create([
            'reviewer_name' => 'Fajar Nugroho',
            'quote' => 'Rekomendasinya relevan.',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('class="quote-card-author"', false)
            ->assertSee('class="quote-card-avatar"', false)
            ->assertSee('src="'.asset('storage/testimonials/rina-pratama.jpg').'"', false)
            ->assertSee('alt="Profil Rina Pratama"', false)
            ->assertSee('quote-card-avatar--fallback', false)
            ->assertSeeInOrder(['Rina Pratama', 'Office Manager', 'Perusahaan Distribusi']);
    }

    public function test_metric_strip_provides_navigation_to_homepage_sections(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-metric-strip', false)
            ->assertSee('href="#produk"', false)
            ->assertSee('href="#artikel"', false)
            ->assertSee('href="#testimoni"', false);
    }

    public function test_hero_product_finder_submits_supported_catalog_filters(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('class="hero-layout"', false)
            ->assertSee('action="'.route('products.index').'"', false)
            ->assertSee('name="category"', false)
            ->assertSee('name="q"', false)
            ->assertSee('Cari produk');
    }
}
