<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\HomepageSetting;
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

    public function test_author_can_upload_valid_inline_image_with_automatic_alt_text(): void
    {
        Storage::fake('public');
        $author = User::factory()->create();
        $post = $this->draftFor($author);
        $image = UploadedFile::fake()->createWithContent('kursi-kantor-di-ruang-rapat.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='))->mimeType('image/png');
        $response = $this->actingAs($author)->postJson(route('cms.posts.media.store', $post), ['image' => $image])->assertOk()->assertJsonStructure(['url', 'alt']);
        $media = $post->media()->first();
        $this->assertNotNull($media);
        Storage::disk('public')->assertExists($media->path);
        $this->assertSame('/storage/'.$media->path, $response->json('url'));
        $this->assertSame('Kursi Kantor Di Ruang Rapat', $media->alt_text);
        $this->assertSame($media->alt_text, $response->json('alt'));
    }

    public function test_cover_alt_text_is_generated_from_article_title(): void
    {
        Storage::fake('public');
        $author = User::factory()->create();
        $post = $this->draftFor($author, 'automatic-cover-alt');
        $image = UploadedFile::fake()->createWithContent('cover.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='))->mimeType('image/png');

        $this->actingAs($author)->put(route('cms.posts.update', $post), [
            'title' => 'Ruang Kerja Modern',
            'excerpt' => 'Ringkasan artikel',
            'body_html' => '<p>Isi artikel</p>',
            'cover_image' => $image,
        ])->assertSessionHasNoErrors();

        $post->refresh();
        $this->assertSame('Ruang Kerja Modern', $post->cover_image_alt);
        Storage::disk('public')->assertExists($post->cover_image_path);
        $this->actingAs($author)->get(route('cms.posts.edit', $post))
            ->assertOk()
            ->assertSee('data-image-dropzone', false)
            ->assertSee('data-image-dropzone-target', false)
            ->assertSee('data-max-size="4194304"', false)
            ->assertSee('accept="image/jpeg,image/png,image/webp"', false)
            ->assertSee('Tarik dan lepaskan gambar di sini')
            ->assertSee('Cover tersimpan dan tetap digunakan.')
            ->assertSee('Ganti cover')
            ->assertSee('/storage/'.$post->cover_image_path, false);
    }

    public function test_upload_rejects_invalid_file(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $post = $this->draftFor($owner, 'media-invalid');
        $file = UploadedFile::fake()->createWithContent('malware.pdf', 'not an image')->mimeType('application/pdf');
        $this->actingAs($owner)->post(route('cms.posts.media.store', $post), ['image' => $file])->assertSessionHasErrors('image');
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

    public function test_homepage_editor_uses_reusable_image_dropzone_for_hero(): void
    {
        $admin = User::factory()->admin()->create();
        $settings = HomepageSetting::current();
        $settings->update([
            'hero_image_path' => 'homepage/hero-office.webp',
            'hero_image_alt' => 'Ruang kantor modern',
        ]);

        $this->actingAs($admin)->get(route('cms.homepage.edit'))
            ->assertOk()
            ->assertSee('data-image-dropzone', false)
            ->assertSee('name="hero_image"', false)
            ->assertSee('accept="image/jpeg,image/png,image/webp"', false)
            ->assertSee('data-current-src="'.asset('storage/homepage/hero-office.webp').'"', false)
            ->assertSee('Gambar hero tersimpan dan tetap digunakan.')
            ->assertSee('Ganti gambar hero')
            ->assertDontSee('id="hero_image_alt"', false)
            ->assertDontSee('Alt text gambar');
    }

    public function test_homepage_hero_alt_text_is_generated_from_title(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $image = UploadedFile::fake()->createWithContent('hero.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='))->mimeType('image/png');

        $this->actingAs($admin)->put(route('cms.homepage.update'), [
            'title' => 'Ruang Kerja yang Produktif',
            'summary' => 'Solusi ruang kerja untuk kebutuhan perusahaan.',
            'hero_image' => $image,
        ])->assertSessionHasNoErrors();

        $settings = HomepageSetting::current();
        $this->assertSame('Ruang Kerja yang Produktif', $settings->hero_image_alt);
        Storage::disk('public')->assertExists($settings->hero_image_path);
    }
}
