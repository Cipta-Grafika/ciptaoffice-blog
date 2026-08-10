<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_inquiry_is_stored(): void
    {
        $this->post(route('contact.store'), ['name' => 'Budi', 'phone' => '08123456789', 'email' => 'budi@example.com', 'message' => 'Butuh sepuluh kursi kantor.', 'website' => ''])->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('inquiries', ['name' => 'Budi', 'status' => 'new']);
    }

    public function test_honeypot_rejects_spam(): void
    {
        $this->post(route('contact.store'), ['name' => 'Bot', 'phone' => '123', 'message' => 'Spam', 'website' => 'https://spam.test'])->assertSessionHasErrors('website');
        $this->assertSame(0, Inquiry::count());
    }

    public function test_contact_form_provides_searchable_active_product_options(): void
    {
        $category = ProductCategory::create(['name' => 'Kursi', 'slug' => 'kursi', 'is_active' => true]);
        $activeProduct = Product::create(['product_category_id' => $category->id, 'name' => 'Kursi Ergo', 'slug' => 'kursi-ergo', 'summary' => 'Nyaman', 'is_active' => true]);
        Product::create(['product_category_id' => $category->id, 'name' => 'Kursi Lama', 'slug' => 'kursi-lama', 'summary' => 'Arsip', 'is_active' => false]);

        $this->get(route('contact.create', ['product' => $activeProduct->id]))
            ->assertOk()
            ->assertSee('data-product-combobox', false)
            ->assertSee('data-product-combobox-input', false)
            ->assertSee('data-product-combobox-listbox', false)
            ->assertSee('value="'.$activeProduct->id.'" selected', false)
            ->assertSee('Kursi Ergo')
            ->assertDontSee('Kursi Lama');
    }
}
