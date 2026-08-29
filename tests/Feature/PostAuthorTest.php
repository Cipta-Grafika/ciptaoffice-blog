<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PostAuthorTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_post_with_manual_author_name_without_changing_owner(): void
    {
        $user = User::factory()->create(['name' => 'Pemilik CMS']);

        $this->actingAs($user)->post(route('cms.posts.store'), [
            'title' => 'Artikel Dengan Byline',
            'author_name' => 'Tim Editorial CiptaOffice',
        ])->assertRedirect()
            ->assertSessionHasNoErrors();

        $post = Post::where('slug', 'artikel-dengan-byline')->firstOrFail();

        $this->assertSame($user->id, $post->author_id);
        $this->assertSame('Tim Editorial CiptaOffice', $post->author_name);
        $this->assertSame('Tim Editorial CiptaOffice', $post->display_author_name);

        $this->actingAs($user)->get(route('cms.posts.edit', $post))
            ->assertOk()
            ->assertSee('name="author_name"', false)
            ->assertSee('value="Tim Editorial CiptaOffice"', false);
    }

    public function test_user_can_update_manual_author_name_without_changing_owner(): void
    {
        $user = User::factory()->create();
        $post = Post::create([
            'author_id' => $user->id,
            'author_name' => 'Byline Lama',
            'title' => 'Artikel Byline',
            'slug' => 'artikel-byline',
            'status' => PostStatus::Draft,
        ]);

        $this->actingAs($user)->put(route('cms.posts.update', $post), [
            'title' => 'Artikel Byline',
            'author_name' => 'Byline Baru',
            'excerpt' => 'Ringkasan artikel.',
            'body_html' => '<p>Isi artikel.</p>',
        ])->assertSessionHasNoErrors();

        $post->refresh();

        $this->assertSame($user->id, $post->author_id);
        $this->assertSame('Byline Baru', $post->author_name);
        $this->assertSame('Byline Baru', $post->display_author_name);
    }

    public function test_display_author_name_falls_back_to_owner_then_site_name(): void
    {
        $user = User::factory()->create(['name' => 'Author Akun']);
        $ownedPost = Post::create([
            'author_id' => $user->id,
            'title' => 'Artikel Akun',
            'slug' => 'artikel-akun',
        ]);
        $unownedPost = Post::create([
            'title' => 'Artikel Tanpa Pemilik',
            'slug' => 'artikel-tanpa-pemilik',
        ]);

        $this->assertSame('Author Akun', $ownedPost->display_author_name);
        $this->assertSame('Tim CiptaOffice', $unownedPost->display_author_name);
    }

    public function test_json_import_uses_author_name_but_keeps_importer_as_owner(): void
    {
        $importer = User::factory()->create(['name' => 'Administrator Import']);
        $json = json_encode([[
            'title' => 'Artikel WordPress',
            'excerpt' => 'Ringkasan artikel WordPress.',
            'body_html' => '<p>Isi artikel WordPress.</p>',
            'author' => [
                'wordpress_id' => 3,
                'name' => 'Fathi Bawazier',
                'slug' => 'fathibawazier1964',
            ],
        ]], JSON_THROW_ON_ERROR);

        $this->actingAs($importer)->post(route('cms.posts.import'), [
            'import_file' => UploadedFile::fake()->createWithContent('artikel.json', $json),
        ])->assertRedirect(route('cms.posts.index'))
            ->assertSessionHasNoErrors();

        $post = Post::where('slug', 'artikel-wordpress')->firstOrFail();

        $this->assertSame($importer->id, $post->author_id);
        $this->assertSame('Fathi Bawazier', $post->author_name);
        $this->assertSame('Fathi Bawazier', $post->display_author_name);

        $this->get(route('articles.show', $post))
            ->assertOk()
            ->assertSee('Fathi Bawazier');
    }

    public function test_csv_import_accepts_manual_author_name(): void
    {
        $importer = User::factory()->create();
        $csv = implode("\n", [
            'title,excerpt,body_html,author_name',
            'Artikel CSV,Ringkasan artikel CSV.,<p>Isi artikel CSV.</p>,Penulis Eksternal',
        ]);

        $this->actingAs($importer)->post(route('cms.posts.import'), [
            'import_file' => UploadedFile::fake()->createWithContent('artikel.csv', $csv),
        ])->assertRedirect(route('cms.posts.index'))
            ->assertSessionHasNoErrors();

        $post = Post::where('slug', 'artikel-csv')->firstOrFail();

        $this->assertSame($importer->id, $post->author_id);
        $this->assertSame('Penulis Eksternal', $post->author_name);
        $this->assertSame('Penulis Eksternal', $post->display_author_name);
    }
}
