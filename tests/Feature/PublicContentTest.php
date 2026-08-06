<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_articles_are_public_and_searchable(): void
    {
        $excerpt = str_repeat('Ringkasan ', 25);
        $published = Post::create(['title' => 'Ergonomi kantor', 'slug' => 'ergonomi-kantor', 'excerpt' => $excerpt, 'body_html' => '<h2>Posisi duduk</h2><p>Aman</p>', 'status' => PostStatus::Published, 'published_at' => now()]);
        $draft = Post::create(['title' => 'Draft', 'slug' => 'draft', 'status' => PostStatus::Draft]);
        $this->get(route('articles.show', $published))
            ->assertOk()
            ->assertSee('<meta name="description" content="'.Str::limit($excerpt, 160).'">', false)
            ->assertSee('data-article-toc', false)
            ->assertSee('Daftar isi');
        $this->get(route('articles.show', $draft))->assertNotFound();
        $this->get(route('articles.index', ['q' => 'Ergonomi']))
            ->assertOk()
            ->assertSee('Ergonomi kantor')
            ->assertSee('data-live-search-form', false)
            ->assertSee('data-live-search-delay="500"', false)
            ->assertSee('data-live-search-clear', false)
            ->assertSee('data-live-search-results', false)
            ->assertSee('data-live-search-status', false)
            ->assertSee('aria-label="Hapus pencarian"', false);

        $this->get(route('articles.index', ['q' => 'Ergonomi']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertSee('Ergonomi kantor')
            ->assertDontSee('data-live-search-form', false)
            ->assertDontSee('<!doctype html>', false);
        $this->get(route('articles.index', ['q' => 'tidak-ada']))->assertDontSee('Ergonomi kantor');
    }

    public function test_article_cover_is_used_in_metadata_and_catalog_thumbnails(): void
    {
        $post = Post::create([
            'title' => 'Ruang Kerja Modern',
            'slug' => 'ruang-kerja-modern',
            'excerpt' => 'Inspirasi ruang kerja modern.',
            'body_html' => '<p>Isi artikel.</p>',
            'cover_image_path' => 'articles/covers/ruang-kerja-modern.png',
            'cover_image_alt' => 'Ruang kerja modern dengan meja kayu',
            'status' => PostStatus::Published,
            'published_at' => now(),
        ]);

        $this->get(route('articles.show', $post))
            ->assertOk()
            ->assertSee('<meta property="og:image" content="'.asset('storage/'.$post->cover_image_path).'">', false)
            ->assertSee('<meta property="og:image:alt" content="'.$post->cover_image_alt.'">', false);

        $this->get(route('articles.index', ['q' => 'Ruang Kerja']))
            ->assertOk()
            ->assertSee('src="'.asset('storage/'.$post->cover_image_path).'"', false)
            ->assertSee('alt="'.$post->cover_image_alt.'"', false);

        $this->get(route('articles.index', ['q' => 'Ruang Kerja']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertSee('src="'.asset('storage/'.$post->cover_image_path).'"', false)
            ->assertSee('alt="'.$post->cover_image_alt.'"', false)
            ->assertDontSee('<!doctype html>', false);
    }

    public function test_active_product_is_public_with_contextual_whatsapp_link(): void
    {
        $category = ProductCategory::create(['name' => 'Kursi', 'slug' => 'kursi', 'is_active' => true]);
        $product = Product::create(['product_category_id' => $category->id, 'name' => 'Kursi Ergo', 'slug' => 'kursi-ergo', 'summary' => 'Nyaman', 'cover_image_path' => 'products/kursi-ergo.png', 'cover_image_alt' => 'Kursi Ergo warna hitam', 'is_active' => true]);
        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Tanyakan ketersediaan')
            ->assertSee('alternatif+berkualitas+setara', false)
            ->assertSee('<meta property="og:image" content="'.asset('storage/'.$product->cover_image_path).'">', false)
            ->assertSee('<meta property="og:image:alt" content="'.$product->cover_image_alt.'">', false);
        $product->update(['is_active' => false]);
        $this->get(route('products.show', $product))->assertNotFound();
    }

    public function test_active_products_are_searchable_within_the_selected_category(): void
    {
        $chairs = ProductCategory::create(['name' => 'Kursi', 'slug' => 'kursi', 'is_active' => true]);
        $desks = ProductCategory::create(['name' => 'Meja', 'slug' => 'meja', 'is_active' => true]);

        Product::create(['product_category_id' => $chairs->id, 'name' => 'Kursi Ergo', 'slug' => 'kursi-ergo', 'summary' => 'Dukungan lumbar', 'is_active' => true]);
        Product::create(['product_category_id' => $chairs->id, 'name' => 'Kursi Direktur', 'slug' => 'kursi-direktur', 'summary' => 'Sandaran premium', 'is_active' => true]);
        Product::create(['product_category_id' => $desks->id, 'name' => 'Meja Ergo', 'slug' => 'meja-ergo', 'summary' => 'Permukaan luas', 'is_active' => true]);
        Product::create(['product_category_id' => $chairs->id, 'name' => 'Kursi Ergo Lama', 'slug' => 'kursi-ergo-lama', 'summary' => 'Tidak ditampilkan', 'is_active' => false]);

        $this->get(route('products.index', ['category' => 'kursi', 'q' => 'Ergo']))
            ->assertOk()
            ->assertSee('Kursi Ergo')
            ->assertDontSee('Kursi Direktur')
            ->assertDontSee('Meja Ergo')
            ->assertDontSee('Kursi Ergo Lama')
            ->assertSee('value="Ergo"', false)
            ->assertSee('name="category" value="kursi"', false)
            ->assertSee('data-live-search-form', false)
            ->assertSee('data-live-search-delay="500"', false)
            ->assertSee('data-live-search-clear', false)
            ->assertSee('data-live-search-param', false)
            ->assertSee('data-live-search-results', false)
            ->assertSee('data-live-search-status', false)
            ->assertSee('aria-label="Hapus pencarian"', false);

        $this->get(route('products.index', ['category' => 'kursi', 'q' => 'Ergo']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertSee('Kursi Ergo')
            ->assertSee('data-live-search-link', false)
            ->assertDontSee('data-live-search-form', false)
            ->assertDontSee('<!doctype html>', false);

        $this->get(route('products.index', ['q' => 'lumbar']))
            ->assertOk()
            ->assertSee('Kursi Ergo');
    }
}
