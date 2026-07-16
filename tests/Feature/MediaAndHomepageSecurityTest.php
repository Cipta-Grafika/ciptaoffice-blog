<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaAndHomepageSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function draftFor(User $author, string $slug = 'media'): Post
    {
        return Post::create(['author_id' => $author->id, 'title' => 'Media', 'slug' => $slug, 'status' => PostStatus::Draft]);
    }

    public function test_author_can_upload_valid_inline_image_with_alt_text(): void
    {
        Storage::fake('public');
        $author = User::factory()->create();
        $post = $this->draftFor($author);
        $image = UploadedFile::fake()->createWithContent('office.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='))->mimeType('image/png');
        $this->actingAs($author)->postJson(route('cms.posts.media.store', $post), ['image' => $image, 'alt_text' => 'Kursi kantor di ruang rapat'])->assertOk()->assertJsonStructure(['url', 'alt']);
        $media = $post->media()->first();
        $this->assertNotNull($media);
        Storage::disk('public')->assertExists($media->path);
        $this->assertSame('Kursi kantor di ruang rapat', $media->alt_text);
    }

    public function test_upload_rejects_invalid_file(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $post = $this->draftFor($owner, 'media-invalid');
        $file = UploadedFile::fake()->createWithContent('malware.pdf', 'not an image')->mimeType('application/pdf');
        $this->actingAs($owner)->post(route('cms.posts.media.store', $post), ['image' => $file, 'alt_text' => 'Dokumen'])->assertSessionHasErrors('image');
    }

    public function test_other_author_cannot_upload_to_post(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $post = $this->draftFor($owner, 'media-private');
        $this->actingAs($other)->postJson(route('cms.posts.media.store', $post), [])->assertForbidden();
    }

    public function test_homepage_rejects_unsafe_cta_url(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->put(route('cms.homepage.update'), ['title' => 'Judul', 'summary' => 'Ringkasan', 'primary_cta_label' => 'Klik', 'primary_cta_url' => 'javascript:alert(1)'])->assertSessionHasErrors('primary_cta_url');
    }
}
