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
