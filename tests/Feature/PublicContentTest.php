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
        $this->get(route('articles.index', ['q' => 'Ergonomi']))->assertSee('Ergonomi kantor');
        $this->get(route('articles.index', ['q' => 'tidak-ada']))->assertDontSee('Ergonomi kantor');
    }

    public function test_active_product_is_public_with_contextual_whatsapp_link(): void
    {
        $category = ProductCategory::create(['name' => 'Kursi', 'slug' => 'kursi', 'is_active' => true]);
        $product = Product::create(['product_category_id' => $category->id, 'name' => 'Kursi Ergo', 'slug' => 'kursi-ergo', 'summary' => 'Nyaman', 'is_active' => true]);
        $this->get(route('products.show', $product))->assertOk()->assertSee('Tanyakan ketersediaan')->assertSee('alternatif+berkualitas+setara', false);
        $product->update(['is_active' => false]);
        $this->get(route('products.show', $product))->assertNotFound();
    }
}
