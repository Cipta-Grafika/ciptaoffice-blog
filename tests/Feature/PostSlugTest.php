<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_post_with_custom_slug_and_redirects_to_new_edit_url(): void
    {
        $user = User::factory()->create();
        $post = Post::create([
            'author_id' => $user->id,
            'title' => 'Artikel Slug Lama',
            'slug' => 'artikel-slug-lama',
            'status' => PostStatus::Draft,
        ]);

        $response = $this->actingAs($user)->put(route('cms.posts.update', $post), [
            'title' => 'Artikel Slug Lama',
            'slug' => 'artikel-slug-baru',
            'excerpt' => 'Ringkasan artikel.',
            'body_html' => '<p>Isi artikel.</p>',
        ]);

        $post->refresh();

        $response->assertRedirect(route('cms.posts.edit', $post))
            ->assertSessionHasNoErrors();

        $this->assertSame('artikel-slug-baru', $post->slug);

        $this->actingAs($user)->get(route('cms.posts.edit', $post))
            ->assertOk()
            ->assertSee('value="artikel-slug-baru"', false);
    }

    public function test_custom_slug_is_automatically_slugified_before_validation(): void
    {
        $user = User::factory()->create();
        $post = Post::create([
            'author_id' => $user->id,
            'title' => 'Artikel Format Slug',
            'slug' => 'artikel-format-slug',
            'status' => PostStatus::Draft,
        ]);

        $this->actingAs($user)->put(route('cms.posts.update', $post), [
            'title' => 'Artikel Format Slug',
            'slug' => '  Soundcore TWS Berkualitas 2026!  ',
            'excerpt' => 'Ringkasan artikel.',
            'body_html' => '<p>Isi artikel.</p>',
        ])->assertSessionHasNoErrors();

        $this->assertSame('soundcore-tws-berkualitas-2026', $post->refresh()->slug);
    }

    public function test_empty_slug_falls_back_to_slugified_title(): void
    {
        $user = User::factory()->create();
        $post = Post::create([
            'author_id' => $user->id,
            'title' => 'Judul Awal',
            'slug' => 'slug-awal',
            'status' => PostStatus::Draft,
        ]);

        $this->actingAs($user)->put(route('cms.posts.update', $post), [
            'title' => 'Panduan Memilih Meja Kerja',
            'slug' => '',
            'excerpt' => 'Ringkasan artikel.',
            'body_html' => '<p>Isi artikel.</p>',
        ])->assertSessionHasNoErrors();

        $this->assertSame('panduan-memilih-meja-kerja', $post->refresh()->slug);
    }

    public function test_user_can_keep_same_slug_when_updating_other_fields(): void
    {
        $user = User::factory()->create();
        $post = Post::create([
            'author_id' => $user->id,
            'title' => 'Judul Lama',
            'slug' => 'slug-tetap',
            'status' => PostStatus::Draft,
        ]);

        $this->actingAs($user)->put(route('cms.posts.update', $post), [
            'title' => 'Judul Baru',
            'slug' => 'slug-tetap',
            'excerpt' => 'Ringkasan terbaru.',
            'body_html' => '<p>Isi terbaru.</p>',
        ])->assertSessionHasNoErrors();

        $post->refresh();

        $this->assertSame('Judul Baru', $post->title);
        $this->assertSame('slug-tetap', $post->slug);
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        $user = User::factory()->create();
        Post::create([
            'author_id' => $user->id,
            'title' => 'Artikel Pertama',
            'slug' => 'slug-terpakai',
            'status' => PostStatus::Draft,
        ]);
        $post = Post::create([
            'author_id' => $user->id,
            'title' => 'Artikel Kedua',
            'slug' => 'artikel-kedua',
            'status' => PostStatus::Draft,
        ]);

        $this->actingAs($user)->put(route('cms.posts.update', $post), [
            'title' => 'Artikel Kedua',
            'slug' => 'slug-terpakai',
            'excerpt' => 'Ringkasan artikel.',
            'body_html' => '<p>Isi artikel.</p>',
        ])->assertSessionHasErrors('slug');

        $this->assertSame('artikel-kedua', $post->refresh()->slug);
    }

    public function test_symbol_only_slug_is_rejected(): void
    {
        $user = User::factory()->create();
        $post = Post::create([
            'author_id' => $user->id,
            'title' => 'Artikel Valid',
            'slug' => 'artikel-valid',
            'status' => PostStatus::Draft,
        ]);

        $this->actingAs($user)->put(route('cms.posts.update', $post), [
            'title' => 'Artikel Valid',
            'slug' => '???!!!',
            'excerpt' => 'Ringkasan artikel.',
            'body_html' => '<p>Isi artikel.</p>',
        ])->assertSessionHasErrors('slug');

        $this->assertSame('artikel-valid', $post->refresh()->slug);
    }
}

