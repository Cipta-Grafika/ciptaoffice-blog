<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TestimonialMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_testimonial_editor_uses_avatar_dropzone_without_alt_text_field(): void
    {
        $admin = User::factory()->admin()->create();
        $testimonial = Testimonial::create([
            'reviewer_name' => 'Rina Pratama',
            'quote' => 'Pelayanan yang sangat responsif.',
            'avatar_path' => 'testimonials/rina-pratama.webp',
            'avatar_alt' => 'Profil Rina Pratama',
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)->get(route('cms.testimonials.edit', $testimonial))
            ->assertOk()
            ->assertSee('data-image-dropzone', false)
            ->assertSee('cms-image-dropzone--avatar', false)
            ->assertSee('name="avatar"', false)
            ->assertSee('data-current-src="'.asset('storage/testimonials/rina-pratama.webp').'"', false)
            ->assertSee('Avatar tersimpan dan tetap digunakan.')
            ->assertSee('800 × 800 piksel')
            ->assertDontSee('name="avatar_alt"', false)
            ->assertDontSee('Alt text avatar');
    }

    public function test_avatar_alt_text_is_generated_from_reviewer_name(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $avatar = UploadedFile::fake()->createWithContent('rina.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='))->mimeType('image/png');

        $this->actingAs($admin)->post(route('cms.testimonials.store'), [
            'reviewer_name' => 'Rina Pratama',
            'quote' => 'Pelayanan yang sangat responsif.',
            'sort_order' => 1,
            'is_active' => 1,
            'avatar' => $avatar,
        ])->assertSessionHasNoErrors();

        $testimonial = Testimonial::firstOrFail();
        $this->assertSame('Profil Rina Pratama', $testimonial->avatar_alt);
        Storage::disk('public')->assertExists($testimonial->avatar_path);
    }
}
