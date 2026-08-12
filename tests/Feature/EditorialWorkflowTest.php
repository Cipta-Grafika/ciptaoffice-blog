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
            ->assertSee('form="post-form"', false)
            ->assertSee('cms-surface-save', false)
            ->assertSee('bi bi-floppy', false)
            ->assertSee('Simpan')
            ->assertDontSee('Simpan perubahan')
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

    public function test_editor_artifacts_are_normalized_without_removing_intentional_alignment(): void
    {
        $author = User::factory()->create();
        $post = Post::create([
            'author_id' => $author->id,
            'title' => 'Normalisasi Editor',
            'slug' => 'normalisasi-editor',
            'status' => PostStatus::Draft,
        ]);

        $nbsp = "\u{00A0}";
        $this->actingAs($author)->put(route('cms.posts.update', $post), [
            'title' => 'Normalisasi Editor',
            'excerpt' => 'Normalisasi artefak editor',
            'body_html' => '<h2 class="ql-align-justify">Judul'.$nbsp.'artikel</h2><p class="ql-align-justify">Isi'.$nbsp.'artikel yang harus tetap membungkus.</p><p class="ql-align-justify"></p>',
        ])->assertSessionHasNoErrors();

        $body = $post->fresh()->body_html;
        $this->assertStringContainsString('Judul artikel', $body);
        $this->assertStringContainsString('Isi artikel yang harus tetap membungkus.', $body);
        $this->assertStringNotContainsString($nbsp, $body);
        $this->assertStringNotContainsString('ql-align-justify', $body);

        $this->actingAs($author)->put(route('cms.posts.update', $post), [
            'title' => 'Normalisasi Editor',
            'excerpt' => 'Normalisasi artefak editor',
            'body_html' => '<h2>Judul normal</h2><p class="ql-align-justify">Paragraf ini memang sengaja dibuat justify.</p>',
        ])->assertSessionHasNoErrors();

        $this->assertStringContainsString(
            'class="ql-align-justify"',
            $post->fresh()->body_html,
        );
    }

    public function test_article_table_structure_is_preserved_without_editor_only_attributes(): void
    {
        $author = User::factory()->create();
        $post = Post::create([
            'author_id' => $author->id,
            'title' => 'Perbandingan Produk',
            'slug' => 'perbandingan-produk',
            'status' => PostStatus::Draft,
        ]);

        $table = '<div class="ql-table-wrapper" contenteditable="false" data-table-id="table-1"><table class="ql-table" data-full="true" style="width: 100%"><colgroup data-full="true"><col width="50%" data-full="true"><col width="50%" data-full="true"></colgroup><thead><tr data-row-id="row-1"><th rowspan="1" colspan="1"><div class="ql-table-cell-inner" data-col-id="col-1"><p>Produk</p></div></th><th rowspan="1" colspan="1"><div class="ql-table-cell-inner"><p>Ukuran</p></div></th></tr></thead><tbody><tr><td rowspan="1" colspan="1"><div class="ql-table-cell-inner"><p>Meja Point</p></div></td><td rowspan="1" colspan="1"><div class="ql-table-cell-inner"><p>120 cm</p></div></td></tr></tbody></table></div>';

        $this->actingAs($author)->put(route('cms.posts.update', $post), [
            'title' => 'Perbandingan Produk',
            'excerpt' => 'Tabel perbandingan produk kantor',
            'body_html' => $table,
        ])->assertSessionHasNoErrors();

        $body = $post->fresh()->body_html;
        $this->assertStringContainsString('class="ql-table-wrapper"', $body);
        $this->assertStringContainsString('<table data-full="true">', $body);
        $this->assertStringContainsString('<colgroup data-full="true">', $body);
        $this->assertSame(2, substr_count($body, '<col width="50%" data-full="true" />'));
        $this->assertStringContainsString('<thead>', $body);
        $this->assertStringContainsString('<tbody>', $body);
        $this->assertStringContainsString('<th rowspan="1" colspan="1">', $body);
        $this->assertStringContainsString('<td rowspan="1" colspan="1">', $body);
        $this->assertStringContainsString('Meja Point', $body);
        $this->assertStringNotContainsString('contenteditable', $body);
        $this->assertStringNotContainsString('data-table-id', $body);
        $this->assertStringNotContainsString('data-col-id', $body);
        $this->assertStringNotContainsString('style=', $body);
        $this->assertStringNotContainsString('ql-table-cell-inner', $body);
    }

    public function test_resized_table_dimensions_are_preserved_safely(): void
    {
        $author = User::factory()->create();
        $post = Post::create([
            'author_id' => $author->id,
            'title' => 'Tabel dengan Ukuran Kustom',
            'slug' => 'tabel-ukuran-kustom',
            'status' => PostStatus::Draft,
        ]);

        $table = '<div class="ql-table-wrapper"><table data-full="true"><colgroup data-full="true"><col width="35%" data-full="true"><col width="65%" data-full="true"></colgroup><tbody><tr><td rowspan="1" colspan="1" style="height:72px;color:red"><div class="ql-table-cell-inner"><p class="ql-align-center">Produk</p></div></td><td rowspan="1" colspan="1" style="height:72px;color:red"><div class="ql-table-cell-inner"><p class="ql-align-center">Ukuran</p></div></td></tr></tbody></table></div>';

        $this->actingAs($author)->put(route('cms.posts.update', $post), [
            'title' => 'Tabel dengan Ukuran Kustom',
            'excerpt' => 'Tabel yang telah diubah ukurannya.',
            'body_html' => $table,
        ])->assertSessionHasNoErrors();

        $body = $post->fresh()->body_html;
        $this->assertStringContainsString('<col width="35%" data-full="true" />', $body);
        $this->assertStringContainsString('<col width="65%" data-full="true" />', $body);
        $this->assertSame(2, substr_count($body, 'height:72px'));
        $this->assertSame(2, substr_count($body, 'class="ql-align-center"'));
        $this->assertStringNotContainsString('color:red', $body);
    }

    public function test_only_supported_video_iframes_are_preserved(): void
    {
        $author = User::factory()->create();
        $post = Post::create([
            'author_id' => $author->id,
            'title' => 'Video Ruang Kerja',
            'slug' => 'video-ruang-kerja',
            'status' => PostStatus::Draft,
        ]);

        $videos = '<p>Video pilihan</p>'
            .'<iframe class="ql-video" src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ" title="Video artikel" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" frameborder="0" allowfullscreen="true"></iframe>'
            .'<iframe class="ql-video" src="https://player.vimeo.com/video/76979871" title="Video artikel" loading="lazy"></iframe>'
            .'<iframe class="ql-video" src="https://evil.example/embed/video" onload="alert(1)"></iframe>';

        $this->actingAs($author)->put(route('cms.posts.update', $post), [
            'title' => 'Video Ruang Kerja',
            'excerpt' => 'Artikel dengan video yang aman.',
            'body_html' => $videos,
        ])->assertSessionHasNoErrors();

        $body = $post->fresh()->body_html;
        $this->assertStringContainsString('class="ql-video"', $body);
        $this->assertStringContainsString('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $body);
        $this->assertStringContainsString('https://player.vimeo.com/video/76979871', $body);
        $this->assertSame(2, substr_count($body, '<iframe'));
        $this->assertStringContainsString('loading="lazy"', $body);
        $this->assertStringNotContainsString('evil.example', $body);
        $this->assertStringNotContainsString('onload', $body);
    }

    public function test_author_cannot_access_admin_modules(): void
    {
        $this->actingAs(User::factory()->create())->get(route('cms.users.index'))->assertForbidden();
    }
}
