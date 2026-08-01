<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductFormValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_form_preserves_all_non_file_fields_after_validation_error(): void
    {
        $admin = User::factory()->admin()->create();
        ProductCategory::create([
            'name' => 'Brankas',
            'slug' => 'brankas',
            'is_active' => true,
        ]);
        $selectedCategory = ProductCategory::create([
            'name' => 'Lemari Arsip',
            'slug' => 'lemari-arsip',
            'is_active' => true,
        ]);
        $product = Product::create([
            'product_category_id' => $selectedCategory->id,
            'name' => 'Produk Awal',
            'slug' => 'produk-awal',
            'summary' => 'Ringkasan awal.',
            'description' => 'Deskripsi awal.',
            'specifications' => ['Material' => 'Kayu'],
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 4,
        ]);

        $editUrl = route('cms.products.edit', $product);
        $response = $this->actingAs($admin)->from($editUrl)->put(route('cms.products.update', $product), [
            'product_category_id' => (string) $selectedCategory->id,
            'name' => 'Nama input lama',
            'summary' => 'Ringkasan input lama.',
            'description' => 'Deskripsi input lama.',
            'specifications_text' => "Material: Besi\nWarna: Hitam",
            'is_featured' => '1',
            'is_active' => '0',
            'sort_order' => '-1',
        ]);

        $response->assertRedirect($editUrl)
            ->assertSessionHasErrors('sort_order');

        $page = $this->get($editUrl)->assertOk();
        $html = $page->getContent();

        $this->assertMatchesRegularExpression(
            '/<option value="'.preg_quote((string) $selectedCategory->id, '/').'" selected>Lemari Arsip<\/option>/',
            $html
        );
        $page->assertSee('value="Nama input lama"', false)
            ->assertSee('>Ringkasan input lama.</textarea>', false)
            ->assertSee('>Deskripsi input lama.</textarea>', false)
            ->assertSee("Material: Besi\nWarna: Hitam</textarea>", false)
            ->assertSee('name="sort_order" value="-1"', false);
        $this->assertMatchesRegularExpression('/<input[^>]+name="is_featured"[^>]+value="1"[^>]+checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input[^>]+name="is_active"[^>]+value="1"[^>]+checked/', $html);

        $product->refresh();
        $this->assertSame('Produk Awal', $product->name);
        $this->assertSame(4, $product->sort_order);
    }
}
