<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

class PostImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_posts_index_contains_accessible_import_dialog(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('cms.posts.index'))
            ->assertOk()
            ->assertSee('data-cms-import-open="postsImportDialog"', false)
            ->assertSee('data-cms-import-modal', false)
            ->assertSee('accept=".csv,.xlsx,.json"', false)
            ->assertSee('<code>title</code>', false)
            ->assertSee('<code>excerpt</code>', false)
            ->assertSee('<code>body_html</code>', false);
    }

    public function test_user_can_import_csv_posts_as_own_drafts(): void
    {
        $user = User::factory()->create();
        Post::create([
            'author_id' => $user->id,
            'title' => 'Panduan Ruang Kerja',
            'slug' => 'panduan-ruang-kerja',
            'status' => PostStatus::Draft,
        ]);

        $csv = implode("\n", [
            'title,excerpt,body_html',
            'Panduan Ruang Kerja,Ringkasan pertama,"<p>Isi aman</p><script>alert(1)</script>"',
            'Memilih Meja Kantor,Ringkasan kedua,<p>Isi artikel kedua</p>',
        ]);

        $response = $this->actingAs($user)->post(route('cms.posts.import'), [
            'import_file' => UploadedFile::fake()->createWithContent('artikel.csv', $csv),
        ]);

        $response->assertRedirect(route('cms.posts.index'))
            ->assertSessionHas('success', '2 artikel berhasil diimpor sebagai draft.');

        $imported = Post::where('title', 'Panduan Ruang Kerja')->where('slug', 'panduan-ruang-kerja-2')->firstOrFail();
        $this->assertSame($user->id, $imported->author_id);
        $this->assertSame(PostStatus::Draft, $imported->status);
        $this->assertStringNotContainsString('<script', $imported->body_html);
        $this->assertDatabaseHas('posts', ['title' => 'Memilih Meja Kantor', 'author_id' => $user->id]);
    }

    public function test_user_can_import_xlsx_posts_with_indonesian_headers(): void
    {
        $user = User::factory()->create();
        $path = tempnam(sys_get_temp_dir(), 'post-import-');
        $writer = new Writer;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(['Judul', 'Ringkasan', 'Isi']));
        $writer->addRow(Row::fromValues([
            'Ergonomi Ruang Kerja',
            'Ringkasan ergonomi.',
            '<p>Isi artikel ergonomi.</p>',
        ]));
        $writer->close();

        try {
            $file = new UploadedFile(
                $path,
                'artikel.xlsx',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                null,
                true,
            );

            $this->actingAs($user)->post(route('cms.posts.import'), ['import_file' => $file])
                ->assertRedirect(route('cms.posts.index'))
                ->assertSessionHasNoErrors();
        } finally {
            @unlink($path);
        }

        $this->assertDatabaseHas('posts', [
            'title' => 'Ergonomi Ruang Kerja',
            'slug' => 'ergonomi-ruang-kerja',
            'author_id' => $user->id,
            'status' => PostStatus::Draft->value,
        ]);
    }

    public function test_user_can_import_json_posts_from_articles_wrapper(): void
    {
        $user = User::factory()->create();
        $json = json_encode([
            'articles' => [
                [
                    'title' => 'Ruang Kerja Produktif',
                    'excerpt' => 'Ringkasan ruang kerja produktif.',
                    'body_html' => '<p>Isi artikel aman.</p><script>alert(1)</script>',
                ],
                [
                    'judul' => 'Kursi Ergonomis',
                    'ringkasan' => 'Ringkasan kursi ergonomis.',
                    'isi' => '<p>Isi artikel ergonomi.</p>',
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $response = $this->actingAs($user)->post(route('cms.posts.import'), [
            'import_file' => UploadedFile::fake()->createWithContent('artikel.json', $json),
        ]);

        $response->assertRedirect(route('cms.posts.index'))
            ->assertSessionHas('success', '2 artikel berhasil diimpor sebagai draft.');

        $firstPost = Post::where('title', 'Ruang Kerja Produktif')->firstOrFail();
        $this->assertSame($user->id, $firstPost->author_id);
        $this->assertSame(PostStatus::Draft, $firstPost->status);
        $this->assertStringNotContainsString('<script', $firstPost->body_html);
        $this->assertDatabaseHas('posts', [
            'title' => 'Kursi Ergonomis',
            'slug' => 'kursi-ergonomis',
        ]);
    }

    public function test_user_can_import_single_json_article(): void
    {
        $user = User::factory()->create();
        $json = json_encode([
            'title' => 'Meja Kerja Minimalis',
            'excerpt' => 'Ringkasan meja kerja.',
            'body_html' => '<p>Isi artikel meja kerja.</p>',
        ], JSON_THROW_ON_ERROR);

        $this->actingAs($user)->post(route('cms.posts.import'), [
            'import_file' => UploadedFile::fake()->createWithContent('artikel.json', $json),
        ])->assertRedirect(route('cms.posts.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('posts', [
            'title' => 'Meja Kerja Minimalis',
            'author_id' => $user->id,
            'status' => PostStatus::Draft->value,
        ]);
    }

    public function test_import_rejects_invalid_json_without_creating_posts(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent(
            'artikel.json',
            '{"articles":[{"title":"JSON rusak"}',
        );

        $this->actingAs($user)->from(route('cms.posts.index'))->post(route('cms.posts.import'), [
            'import_file' => $file,
        ])->assertRedirect(route('cms.posts.index'))
            ->assertSessionHasErrors('import_file', null, 'postImport');

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_import_rejects_json_item_with_missing_required_fields(): void
    {
        $user = User::factory()->create();
        $json = json_encode([
            ['title' => 'Artikel tanpa isi', 'excerpt' => 'Ringkasan'],
        ], JSON_THROW_ON_ERROR);

        $this->actingAs($user)->from(route('cms.posts.index'))->post(route('cms.posts.import'), [
            'import_file' => UploadedFile::fake()->createWithContent('artikel.json', $json),
        ])->assertRedirect(route('cms.posts.index'))
            ->assertSessionHasErrors('import_file', null, 'postImport');

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_import_rejects_missing_columns_without_creating_posts(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent(
            'artikel.csv',
            "title,excerpt\nArtikel tanpa isi,Ringkasan",
        );

        $this->actingAs($user)->from(route('cms.posts.index'))->post(route('cms.posts.import'), [
            'import_file' => $file,
        ])->assertRedirect(route('cms.posts.index'))
            ->assertSessionHasErrors('import_file', null, 'postImport');

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_import_rejects_unsupported_file_format(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from(route('cms.posts.index'))->post(route('cms.posts.import'), [
            'import_file' => UploadedFile::fake()->create('artikel.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('cms.posts.index'))
            ->assertSessionHasErrors('import_file', null, 'postImport');
    }

    public function test_guest_cannot_import_posts(): void
    {
        $this->post(route('cms.posts.import'))->assertRedirect(route('login'));
    }
}
