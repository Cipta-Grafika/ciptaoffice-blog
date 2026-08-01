<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product_gallery_and_first_image_becomes_thumbnail(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $category = $this->category();

        $response = $this->actingAs($admin)->post(route('cms.products.store'), [
            ...$this->productData($category),
            'product_images' => [
                $this->image('thumbnail.png'),
                $this->image('detail-front.png'),
                $this->image('detail-side.png'),
            ],
        ]);

        $response->assertRedirect(route('cms.products.index'))->assertSessionHasNoErrors();

        $product = Product::where('slug', 'meja-signature')->firstOrFail();
        $this->assertNotNull($product->cover_image_path);
        $this->assertSame('Meja Signature', $product->cover_image_alt);
        $this->assertCount(2, $product->images);
        $this->assertSame([1, 2], $product->images->pluck('sort_order')->all());
        Storage::disk('public')->assertExists($product->cover_image_path);

        foreach ($product->images as $image) {
            Storage::disk('public')->assertExists($image->path);
        }

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('class="carousel slide product-gallery"', false)
            ->assertSee('/storage/'.$product->cover_image_path, false)
            ->assertSee('/storage/'.$product->images->first()->path, false)
            ->assertSee('Tampilkan gambar 3');
    }

    public function test_uploading_a_new_set_replaces_old_product_gallery_and_files(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $category = $this->category();

        $this->actingAs($admin)->post(route('cms.products.store'), [
            ...$this->productData($category),
            'product_images' => [
                $this->image('old-thumbnail.png'),
                $this->image('old-detail.png'),
            ],
        ])->assertSessionHasNoErrors();

        $product = Product::where('slug', 'meja-signature')->firstOrFail();
        $oldPaths = [$product->cover_image_path, ...$product->images->pluck('path')->all()];

        $this->actingAs($admin)->put(route('cms.products.update', $product), [
            ...$this->productData($category),
            'product_images' => [
                $this->image('new-thumbnail.png'),
                $this->image('new-detail.png'),
            ],
        ])->assertRedirect(route('cms.products.index'))->assertSessionHasNoErrors();

        $product->refresh()->load('images');
        $this->assertSame('Meja Signature', $product->cover_image_alt);

        $this->assertCount(1, $product->images);
        $this->assertNotContains($product->cover_image_path, $oldPaths);
        Storage::disk('public')->assertExists($product->cover_image_path);
        Storage::disk('public')->assertExists($product->images->first()->path);

        foreach ($oldPaths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }

    public function test_admin_can_delete_one_carousel_image_and_remaining_images_are_resequenced(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $category = $this->category();

        $this->actingAs($admin)->post(route('cms.products.store'), [
            ...$this->productData($category),
            'product_images' => [
                $this->image('thumbnail.png'),
                $this->image('detail-one.png'),
                $this->image('detail-two.png'),
                $this->image('detail-three.png'),
            ],
        ])->assertSessionHasNoErrors();

        $product = Product::where('slug', 'meja-signature')->firstOrFail();
        $product->load('images');
        $deletedImage = $product->images[1];
        $deletedPath = $deletedImage->path;
        $remainingPaths = [$product->images[0]->path, $product->images[2]->path];

        $this->actingAs($admin)->get(route('cms.products.edit', $product))
            ->assertOk()
            ->assertSee(route('cms.products.thumbnail.destroy', $product), false)
            ->assertSee(route('cms.products.images.destroy', [$product, $deletedImage]), false)
            ->assertSee('<figcaption>Thumbnail</figcaption>', false)
            ->assertSee('<figcaption><span>01</span>Carousel</figcaption>', false)
            ->assertDontSee('<span>01</span>Thumbnail', false)
            ->assertSee('data-product-gallery-sortable', false)
            ->assertSee('data-gallery-token="thumbnail"', false)
            ->assertSee('name="gallery_order[]"', false)
            ->assertSee('aria-label="Hapus gambar carousel 2"', false);

        $this->actingAs($admin)
            ->delete(route('cms.products.images.destroy', [$product, $deletedImage]))
            ->assertRedirect(route('cms.products.edit', $product))
            ->assertSessionHas('success', 'Gambar carousel dihapus.');

        $product->refresh()->load('images');

        $this->assertSame($remainingPaths, $product->images->pluck('path')->all());
        $this->assertSame([1, 2], $product->images->pluck('sort_order')->all());
        $this->assertSame([
            'Meja Signature — gambar 2',
            'Meja Signature — gambar 3',
        ], $product->images->pluck('alt_text')->all());
        Storage::disk('public')->assertMissing($deletedPath);
        Storage::disk('public')->assertExists($product->cover_image_path);

        foreach ($remainingPaths as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_deleting_thumbnail_promotes_first_carousel_image_without_deleting_its_file(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $category = $this->category();

        $this->actingAs($admin)->post(route('cms.products.store'), [
            ...$this->productData($category),
            'product_images' => [
                $this->image('thumbnail.png'),
                $this->image('detail-one.png'),
                $this->image('detail-two.png'),
            ],
        ])->assertSessionHasNoErrors();

        $product = Product::where('slug', 'meja-signature')->firstOrFail();
        $product->load('images');
        $oldThumbnailPath = $product->cover_image_path;
        $promotedPath = $product->images[0]->path;
        $remainingPath = $product->images[1]->path;

        $this->actingAs($admin)
            ->delete(route('cms.products.thumbnail.destroy', $product))
            ->assertRedirect(route('cms.products.edit', $product))
            ->assertSessionHas('success', 'Thumbnail dihapus. Gambar carousel pertama dijadikan thumbnail baru.');

        $product->refresh()->load('images');

        $this->assertSame($promotedPath, $product->cover_image_path);
        $this->assertSame('Meja Signature', $product->cover_image_alt);
        $this->assertCount(1, $product->images);
        $this->assertSame($remainingPath, $product->images->first()->path);
        $this->assertSame(1, $product->images->first()->sort_order);
        $this->assertSame('Meja Signature — gambar 2', $product->images->first()->alt_text);
        Storage::disk('public')->assertMissing($oldThumbnailPath);
        Storage::disk('public')->assertExists($promotedPath);
        Storage::disk('public')->assertExists($remainingPath);
    }

    public function test_deleting_only_thumbnail_leaves_product_without_gallery(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('cms.products.store'), [
            ...$this->productData($this->category()),
            'product_images' => [$this->image('thumbnail.png')],
        ])->assertSessionHasNoErrors();

        $product = Product::where('slug', 'meja-signature')->firstOrFail();
        $deletedPath = $product->cover_image_path;

        $this->actingAs($admin)
            ->delete(route('cms.products.thumbnail.destroy', $product))
            ->assertRedirect(route('cms.products.edit', $product));

        $product->refresh();

        $this->assertNull($product->cover_image_path);
        $this->assertNull($product->cover_image_alt);
        $this->assertCount(0, $product->images);
        Storage::disk('public')->assertMissing($deletedPath);
    }

    public function test_carousel_image_cannot_be_deleted_through_another_product_or_by_non_admin(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $category = $this->category();
        $product = Product::create([
            'product_category_id' => $category->id,
            'name' => 'Produk Satu',
            'slug' => 'produk-satu',
            'summary' => 'Produk pertama.',
            'specifications' => [],
            'is_active' => true,
            'sort_order' => 0,
        ]);
        $otherProduct = Product::create([
            'product_category_id' => $category->id,
            'name' => 'Produk Dua',
            'slug' => 'produk-dua',
            'summary' => 'Produk kedua.',
            'specifications' => [],
            'is_active' => true,
            'sort_order' => 0,
        ]);
        $foreignImage = $otherProduct->images()->create([
            'path' => 'products/foreign/image.png',
            'alt_text' => 'Produk Dua — gambar 2',
            'sort_order' => 1,
        ]);
        Storage::disk('public')->put($foreignImage->path, 'image');

        $this->actingAs($admin)
            ->delete(route('cms.products.images.destroy', [$product, $foreignImage]))
            ->assertNotFound();

        $this->assertDatabaseHas('product_images', ['id' => $foreignImage->id]);
        Storage::disk('public')->assertExists($foreignImage->path);

        $this->actingAs(User::factory()->create())
            ->delete(route('cms.products.images.destroy', [$otherProduct, $foreignImage]))
            ->assertForbidden();

        $this->assertDatabaseHas('product_images', ['id' => $foreignImage->id]);
        Storage::disk('public')->assertExists($foreignImage->path);
    }

    public function test_admin_can_reorder_gallery_and_promote_carousel_to_thumbnail(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $category = $this->category();

        $this->actingAs($admin)->post(route('cms.products.store'), [
            ...$this->productData($category),
            'product_images' => [
                $this->image('thumbnail.png'),
                $this->image('detail-one.png'),
                $this->image('detail-two.png'),
                $this->image('detail-three.png'),
            ],
        ])->assertSessionHasNoErrors();

        $product = Product::where('slug', 'meja-signature')->firstOrFail();
        $product->load('images');
        $oldThumbnailPath = $product->cover_image_path;
        $firstCarousel = $product->images[0];
        $newThumbnail = $product->images[1];
        $lastCarousel = $product->images[2];
        $allPaths = [$oldThumbnailPath, ...$product->images->pluck('path')->all()];

        $this->actingAs($admin)->put(route('cms.products.update', $product), [
            ...$this->productData($category),
            'gallery_order' => [
                'image:'.$newThumbnail->id,
                'thumbnail',
                'image:'.$lastCarousel->id,
                'image:'.$firstCarousel->id,
            ],
        ])->assertRedirect(route('cms.products.index'))->assertSessionHasNoErrors();

        $product->refresh()->load('images');

        $this->assertSame($newThumbnail->path, $product->cover_image_path);
        $this->assertSame('Meja Signature', $product->cover_image_alt);
        $this->assertSame([
            $oldThumbnailPath,
            $lastCarousel->path,
            $firstCarousel->path,
        ], $product->images->pluck('path')->all());
        $this->assertSame([1, 2, 3], $product->images->pluck('sort_order')->all());
        $this->assertSame([
            'Meja Signature — gambar 2',
            'Meja Signature — gambar 3',
            'Meja Signature — gambar 4',
        ], $product->images->pluck('alt_text')->all());

        foreach ($allPaths as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_incomplete_gallery_order_is_rejected_without_changing_product_or_gallery(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $category = $this->category();

        $this->actingAs($admin)->post(route('cms.products.store'), [
            ...$this->productData($category),
            'product_images' => [
                $this->image('thumbnail.png'),
                $this->image('detail-one.png'),
                $this->image('detail-two.png'),
            ],
        ])->assertSessionHasNoErrors();

        $product = Product::where('slug', 'meja-signature')->firstOrFail();
        $product->load('images');
        $originalThumbnailPath = $product->cover_image_path;
        $originalImagePaths = $product->images->pluck('path')->all();

        $this->actingAs($admin)->put(route('cms.products.update', $product), [
            ...$this->productData($category),
            'name' => 'Nama Tidak Boleh Tersimpan',
            'gallery_order' => [
                'image:'.$product->images[0]->id,
                'thumbnail',
            ],
        ])->assertSessionHasErrors('gallery_order');

        $product->refresh()->load('images');

        $this->assertSame('Meja Signature', $product->name);
        $this->assertSame($originalThumbnailPath, $product->cover_image_path);
        $this->assertSame($originalImagePaths, $product->images->pluck('path')->all());

        foreach ([$originalThumbnailPath, ...$originalImagePaths] as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_product_gallery_rejects_more_than_eight_images(): void
    {
        Storage::fake('public');

        $images = [];
        foreach (range(1, 9) as $index) {
            $images[] = $this->image('product-'.$index.'.png');
        }

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('cms.products.store'), [
                ...$this->productData($this->category()),
                'product_images' => $images,
            ])
            ->assertSessionHasErrors('product_images');

        $this->assertDatabaseCount('products', 0);
    }

    private function image(string $name): UploadedFile
    {
        return UploadedFile::fake()
            ->createWithContent(
                $name,
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
            )
            ->mimeType('image/png');
    }

    private function category(): ProductCategory
    {
        return ProductCategory::create([
            'name' => 'Meja Kantor',
            'slug' => 'meja-kantor',
            'is_active' => true,
        ]);
    }

    private function productData(ProductCategory $category): array
    {
        return [
            'product_category_id' => $category->id,
            'name' => 'Meja Signature',
            'summary' => 'Meja kerja untuk kebutuhan ruang profesional.',
            'description' => 'Dirancang untuk penggunaan harian.',
            'specifications_text' => "Material: Kayu olahan\nWarna: Natural",
            'is_featured' => '0',
            'is_active' => '1',
            'sort_order' => '0',
        ];
    }
}
