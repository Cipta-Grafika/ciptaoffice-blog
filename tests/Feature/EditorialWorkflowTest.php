<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_submit_and_admin_can_publish_sanitized_article(): void
    {
        $author = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $post = Post::create(['author_id' => $author->id, 'title' => 'Draft Aman', 'slug' => 'draft-aman', 'status' => PostStatus::Draft]);
        $this->actingAs($author)->put(route('cms.posts.update', $post), ['title' => 'Draft Aman', 'excerpt' => 'Ringkasan yang cukup', 'body_html' => '<p onclick="alert(1)">Isi aman</p><script>alert(1)</script>'])->assertSessionHasNoErrors();
        $post->refresh();
        $this->assertStringNotContainsString('script', $post->body_html);
        $this->assertStringNotContainsString('onclick', $post->body_html);
        $this->actingAs($author)->post(route('cms.posts.submit', $post))->assertSessionHasNoErrors();
        $this->assertSame(PostStatus::PendingReview, $post->fresh()->status);
        $this->actingAs($admin)->post(route('cms.posts.publish', $post))->assertSessionHasNoErrors();
        $this->assertSame(PostStatus::Published, $post->fresh()->status);
        $this->get(route('articles.show', $post))->assertOk()->assertSee('Draft Aman');
    }

    public function test_admin_can_return_article_with_required_note_and_author_cannot_edit_others(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $post = Post::create(['author_id' => $author->id, 'title' => 'Review', 'slug' => 'review', 'excerpt' => 'Ringkas', 'body_html' => '<p>Isi</p>', 'status' => PostStatus::PendingReview]);
        $this->actingAs($admin)->post(route('cms.posts.return', $post), [])->assertSessionHasErrors('review_note');
        $this->actingAs($admin)->post(route('cms.posts.return', $post), ['review_note' => 'Perjelas sumber data.'])->assertSessionHasNoErrors();
        $this->assertSame(PostStatus::Returned, $post->fresh()->status);
        $this->assertSame('Perjelas sumber data.', $post->fresh()->review_note);
        $this->actingAs($other)->get(route('cms.posts.edit', $post))->assertForbidden();
    }

    public function test_published_article_cannot_be_submitted_for_review_again(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::create([
            'author_id' => $admin->id,
            'title' => 'Artikel Terbit',
            'slug' => 'artikel-terbit',
            'excerpt' => 'Ringkasan artikel terbit',
            'body_html' => '<p>Isi artikel terbit.</p>',
            'status' => PostStatus::Published,
            'published_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('cms.posts.edit', $post))
            ->assertOk()
            ->assertDontSee('Ajukan review');

        $this->actingAs($admin)->post(route('cms.posts.submit', $post))
            ->assertUnprocessable();

        $this->assertSame(PostStatus::Published, $post->fresh()->status);
    }

    public function test_article_excerpt_accepts_1000_characters_and_rejects_longer_content(): void
    {
        $author = User::factory()->create();
        $post = Post::create([
            'author_id' => $author->id,
            'title' => 'Batas Ringkasan',
            'slug' => 'batas-ringkasan',
            'status' => PostStatus::Draft,
        ]);
        $validExcerpt = str_repeat('a', 1000);

        $this->actingAs($author)->put(route('cms.posts.update', $post), [
            'title' => 'Batas Ringkasan',
            'excerpt' => $validExcerpt,
            'body_html' => '<p>Isi artikel.</p>',
        ])->assertSessionHasNoErrors();

        $this->assertSame($validExcerpt, $post->fresh()->excerpt);

        $this->actingAs($author)->put(route('cms.posts.update', $post), [
            'title' => 'Batas Ringkasan',
            'excerpt' => str_repeat('a', 1001),
            'body_html' => '<p>Isi artikel.</p>',
        ])->assertSessionHasErrors('excerpt');
    }

    public function test_author_can_preview_own_draft_but_cannot_preview_another_authors_draft(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::create([
            'author_id' => $author->id,
            'title' => 'Preview Ruang Kerja',
            'slug' => 'preview-ruang-kerja',
            'excerpt' => 'Ringkasan preview',
            'body_html' => '<h2>Isi preview</h2><p>Konten yang sudah disimpan.</p>',
            'status' => PostStatus::Draft,
        ]);

        $this->actingAs($author)->get(route('cms.posts.preview', $post))
            ->assertOk()
            ->assertSee('Mode preview')
            ->assertSee('Belum diterbitkan')
            ->assertSee('Isi preview')
            ->assertSee('data-article-toc', false)
            ->assertSee('Daftar isi')
            ->assertSee('noindex,nofollow', false);

        $this->actingAs($other)->get(route('cms.posts.preview', $post))->assertForbidden();
    }

    public function test_bullet_list_remains_unordered_after_article_is_saved(): void
    {
        $author = User::factory()->create();
        $post = Post::create([
            'author_id' => $author->id,
            'title' => 'Daftar Kebutuhan',
            'slug' => 'daftar-kebutuhan',
            'status' => PostStatus::Draft,
        ]);

        $this->actingAs($author)->put(route('cms.posts.update', $post), [
            'title' => 'Daftar Kebutuhan',
            'excerpt' => 'Daftar perlengkapan kantor',
            'body_html' => '<h2>Kebutuhan utama</h2><ul><li>Meja kantor</li><li>Kursi ergonomis</li></ul>',
        ])->assertSessionHasNoErrors();

        $body = $post->fresh()->body_html;
        $this->assertStringContainsString('<ul>', $body);
        $this->assertStringContainsString('<li>Meja kantor</li>', $body);
        $this->assertStringNotContainsString('<ol>', $body);
    }

    public function test_article_indentation_and_alignment_are_preserved_and_unapproved_classes_are_removed(): void
    {
        $author = User::factory()->create();
        $post = Post::create([
            'author_id' => $author->id,
            'title' => 'Panduan Ruang Kerja',
            'slug' => 'panduan-ruang-kerja',
            'status' => PostStatus::Draft,
        ]);

        $this->actingAs($author)->put(route('cms.posts.update', $post), [
            'title' => 'Panduan Ruang Kerja',
            'excerpt' => 'Panduan menata ruang kerja',
            'body_html' => '<h1>Judul utama isi</h1><p class="ql-indent-2 class-tidak-diizinkan">Paragraf menjorok</p><h2 class="ql-align-center">Judul tengah</h2><p class="ql-align-right">Paragraf kanan</p><p class="ql-align-justify">Paragraf rata kiri dan kanan</p>',
        ])->assertSessionHasNoErrors();

        $body = $post->fresh()->body_html;
        $this->assertStringContainsString('<h1>Judul utama isi</h1>', $body);
        $this->assertStringContainsString('class="ql-indent-2"', $body);
        $this->assertStringContainsString('class="ql-align-center"', $body);
        $this->assertStringContainsString('class="ql-align-right"', $body);
        $this->assertStringContainsString('class="ql-align-justify"', $body);
        $this->assertStringNotContainsString('class-tidak-diizinkan', $body);
    }

    public function test_author_cannot_access_admin_modules(): void
    {
        $this->actingAs(User::factory()->create())->get(route('cms.users.index'))->assertForbidden();
    }
}
