<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PostMediaImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_json_import_persists_cover_and_inline_media_manifest(): void
    {
        $user = User::factory()->create();
        $inlinePath = 'articles/8950/inline/abc123.webp';
        $json = json_encode([
            'articles' => [[
                'title' => 'Migrasi Media',
                'excerpt' => 'Ringkasan migrasi media.',
                'body_html' => '<p><img src="/storage/'.$inlinePath.'" alt="Kursi"></p>',
                'cover_image_path' => 'articles/covers/8950-cover.webp',
                'cover_image_alt' => 'Cover migrasi media',
                'inline_images' => [[
                    'storage_path' => $inlinePath,
                    'alt_text' => 'Kursi kantor',
                    'source_url' => 'https://ciptaoffice.com/example.webp',
                ]],
            ]],
        ], JSON_THROW_ON_ERROR);

        $this->actingAs($user)->post(route('cms.posts.import'), [
            'import_file' => UploadedFile::fake()->createWithContent('media.json', $json),
        ])->assertRedirect(route('cms.posts.index'))
            ->assertSessionHasNoErrors();

        $post = Post::where('slug', 'migrasi-media')->firstOrFail();
        $this->assertSame('articles/covers/8950-cover.webp', $post->cover_image_path);
        $this->assertSame('Cover migrasi media', $post->cover_image_alt);
        $this->assertStringContainsString('/storage/'.$inlinePath, $post->body_html);
        $this->assertDatabaseHas('post_media', [
            'post_id' => $post->id,
            'uploaded_by' => $user->id,
            'path' => $inlinePath,
            'alt_text' => 'Kursi kantor',
        ]);
    }

    public function test_json_import_rejects_media_path_traversal(): void
    {
        $user = User::factory()->create();
        $json = json_encode([[
            'title' => 'Media Tidak Aman',
            'excerpt' => 'Ringkasan.',
            'body_html' => '<p>Isi artikel.</p>',
            'cover_image_path' => 'articles/covers/../../.env',
            'cover_image_alt' => 'Tidak aman',
        ]], JSON_THROW_ON_ERROR);

        $this->actingAs($user)->from(route('cms.posts.index'))->post(route('cms.posts.import'), [
            'import_file' => UploadedFile::fake()->createWithContent('media.json', $json),
        ])->assertRedirect(route('cms.posts.index'))
            ->assertSessionHasErrors('import_file', null, 'postImport');

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_json_import_rejects_unreferenced_inline_media(): void
    {
        $user = User::factory()->create();
        $json = json_encode([[
            'title' => 'Manifest Tidak Konsisten',
            'excerpt' => 'Ringkasan.',
            'body_html' => '<p>Isi tanpa gambar.</p>',
            'inline_images' => [[
                'storage_path' => 'articles/10/inline/image.webp',
                'alt_text' => 'Tidak direferensikan',
            ]],
        ]], JSON_THROW_ON_ERROR);

        $this->actingAs($user)->from(route('cms.posts.index'))->post(route('cms.posts.import'), [
            'import_file' => UploadedFile::fake()->createWithContent('media.json', $json),
        ])->assertRedirect(route('cms.posts.index'))
            ->assertSessionHasErrors('import_file', null, 'postImport');

        $this->assertDatabaseCount('posts', 0);
        $this->assertDatabaseCount('post_media', 0);
    }
}
