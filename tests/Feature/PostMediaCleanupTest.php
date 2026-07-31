<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostMediaCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_removing_saved_inline_image_deletes_file_and_empty_directories(): void
    {
        Storage::fake('public');
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $author = User::factory()->create();
        $post = Post::create([
            'author_id' => $author->id,
            'title' => 'Media Cleanup',
            'slug' => 'media-cleanup',
            'status' => PostStatus::Draft,
        ]);
        $image = UploadedFile::fake()
            ->createWithContent(
                'ruang-kerja.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
            )
            ->mimeType('image/png');

        $upload = $this->actingAs($author)
            ->postJson(route('cms.posts.media.store', $post), ['image' => $image])
            ->assertOk();

        /** @var PostMedia $media */
        $media = $post->media()->sole();
        $inlineDirectory = 'articles/'.$post->id.'/inline';
        $postDirectory = 'articles/'.$post->id;

        $this->actingAs($author)->put(route('cms.posts.update', $post), [
            'title' => $post->title,
            'excerpt' => 'Ringkasan media cleanup',
            'body_html' => '<p>Isi artikel</p><img src="'.$upload->json('url').'" alt="'.$media->alt_text.'">',
        ])->assertSessionHasNoErrors();

        $disk->assertExists($media->path);
        $this->assertTrue($disk->directoryExists($inlineDirectory));

        $this->actingAs($author)->put(route('cms.posts.update', $post), [
            'title' => $post->title,
            'excerpt' => 'Ringkasan media cleanup',
            'body_html' => '<p>Gambar telah dihapus.</p>',
        ])->assertSessionHasNoErrors();

        $disk->assertMissing($media->path);
        $this->assertDatabaseMissing('post_media', ['id' => $media->id]);
        $this->assertFalse($disk->directoryExists($inlineDirectory));
        $this->assertFalse($disk->directoryExists($postDirectory));
    }

    public function test_admin_can_permanently_delete_post_and_all_media(): void
    {
        Storage::fake('public');
        /**  FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $admin = User::factory()->admin()->create();
        $post = Post::create([
            'author_id' => $admin->id,
            'title' => 'Permanent Cleanup',
            'slug' => 'permanent-cleanup',
            'excerpt' => 'Ringkasan permanent cleanup',
            'body_html' => '<p>Isi artikel.</p>',
            'cover_image_path' => 'articles/covers/permanent-cleanup.png',
            'status' => PostStatus::Draft,
        ]);
        $inlinePath = "articles/{$post->id}/inline/ruang-kerja.png";
        $disk->put($post->cover_image_path, 'cover');
        $disk->put($inlinePath, 'inline');
        $media = PostMedia::create([
            'post_id' => $post->id,
            'uploaded_by' => $admin->id,
            'path' => $inlinePath,
            'alt_text' => 'Ruang kerja',
        ]);

        $this->actingAs($admin)->get(route('cms.posts.edit', $post))
            ->assertOk()
            ->assertSee('Trash')
            ->assertSee('Delete');

        $this->actingAs($admin)->delete(route('cms.posts.force-delete', $post))
            ->assertRedirect(route('cms.posts.index'))
            ->assertSessionHas('success', 'Artikel dihapus secara permanen.');

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('post_media', ['id' => $media->id]);
        $disk->assertMissing($post->cover_image_path);
        $disk->assertMissing($inlinePath);
        $this->assertFalse($disk->directoryExists("articles/{$post->id}"));
    }

    public function test_author_cannot_permanently_delete_post(): void
    {
        $author = User::factory()->create();
        $post = Post::create([
            'author_id' => $author->id,
            'title' => 'Protected Post',
            'slug' => 'protected-post',
            'status' => PostStatus::Draft,
        ]);

        $this->actingAs($author)->delete(route('cms.posts.force-delete', $post))
            ->assertForbidden();

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'deleted_at' => null]);
    }

    public function test_pruner_removes_only_empty_numeric_article_directories(): void
    {
        Storage::fake('public');
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $disk->makeDirectory('articles/10/inline');
        $disk->makeDirectory('articles/covers');

        PostMedia::pruneEmptyArticleDirectories();

        $this->assertFalse($disk->directoryExists('articles/10'));
        $this->assertTrue($disk->directoryExists('articles/covers'));
    }
}
