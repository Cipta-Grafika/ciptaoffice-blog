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
}
