<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Inquiry;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsPaginationSortingTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_paginated_cms_index_orders_records_from_newest_to_oldest(): void
    {
        $admin = User::factory()->admin()->create(['created_at' => now()->subDays(10)]);
        $olderAt = now()->subDays(2);
        $newerAt = now()->subDay();

        $olderUser = User::factory()->create([
            'name' => 'Alpha Lama',
            'created_at' => $olderAt,
            'updated_at' => $olderAt,
        ]);
        $newerUser = User::factory()->create([
            'name' => 'Zulu Baru',
            'created_at' => $newerAt,
            'updated_at' => $newerAt,
        ]);

        $olderTestimonial = Testimonial::create([
            'reviewer_name' => 'Testimoni Lama',
            'quote' => 'Testimoni yang dibuat lebih dahulu.',
            'sort_order' => 0,
            'created_at' => $olderAt,
            'updated_at' => $olderAt,
        ]);
        $newerTestimonial = Testimonial::create([
            'reviewer_name' => 'Testimoni Baru',
            'quote' => 'Testimoni yang dibuat kemudian.',
            'sort_order' => 100,
            'created_at' => $newerAt,
            'updated_at' => $newerAt,
        ]);

        $olderInquiry = Inquiry::create([
            'name' => 'Inquiry Lama',
            'phone' => '081111111111',
            'message' => 'Pertanyaan lama.',
            'created_at' => $olderAt,
            'updated_at' => $olderAt,
        ]);
        $newerInquiry = Inquiry::create([
            'name' => 'Inquiry Baru',
            'phone' => '082222222222',
            'message' => 'Pertanyaan baru.',
            'created_at' => $newerAt,
            'updated_at' => $newerAt,
        ]);

        $olderPost = Post::create([
            'author_id' => $admin->id,
            'title' => 'Artikel Lama',
            'slug' => 'artikel-lama',
            'status' => PostStatus::Draft,
            'created_at' => $olderAt,
            'updated_at' => $olderAt,
        ]);
        $newerPost = Post::create([
            'author_id' => $admin->id,
            'title' => 'Artikel Baru',
            'slug' => 'artikel-baru',
            'status' => PostStatus::Draft,
            'created_at' => $newerAt,
            'updated_at' => $newerAt,
        ]);

        $olderCategory = ProductCategory::create([
            'name' => 'Kategori Lama',
            'slug' => 'kategori-lama',
            'sort_order' => 0,
            'created_at' => $olderAt,
            'updated_at' => $olderAt,
        ]);
        $newerCategory = ProductCategory::create([
            'name' => 'Kategori Baru',
            'slug' => 'kategori-baru',
            'sort_order' => 100,
            'created_at' => $newerAt,
            'updated_at' => $newerAt,
        ]);

        $olderProduct = Product::create([
            'product_category_id' => $olderCategory->id,
            'name' => 'Produk Lama',
            'slug' => 'produk-lama',
            'summary' => 'Produk yang dibuat lebih dahulu.',
            'sort_order' => 0,
            'created_at' => $olderAt,
            'updated_at' => $olderAt,
        ]);
        $newerProduct = Product::create([
            'product_category_id' => $newerCategory->id,
            'name' => 'Produk Baru',
            'slug' => 'produk-baru',
            'summary' => 'Produk yang dibuat kemudian.',
            'sort_order' => 100,
            'created_at' => $newerAt,
            'updated_at' => $newerAt,
        ]);

        $pages = [
            ['cms.users.index', 'users', $olderUser->id, $newerUser->id],
            ['cms.testimonials.index', 'testimonials', $olderTestimonial->id, $newerTestimonial->id],
            ['cms.inquiries.index', 'inquiries', $olderInquiry->id, $newerInquiry->id],
            ['cms.posts.index', 'posts', $olderPost->id, $newerPost->id],
            ['cms.categories.index', 'categories', $olderCategory->id, $newerCategory->id],
            ['cms.products.index', 'products', $olderProduct->id, $newerProduct->id],
        ];

        foreach ($pages as [$routeName, $viewVariable, $olderId, $newerId]) {
            $paginator = $this->actingAs($admin)
                ->get(route($routeName))
                ->assertOk()
                ->viewData($viewVariable);

            $ids = collect($paginator->items())->pluck('id')->all();
            $olderIndex = array_search($olderId, $ids, true);
            $newerIndex = array_search($newerId, $ids, true);

            $this->assertIsInt($olderIndex, "Record lama tidak ditemukan pada {$routeName}.");
            $this->assertIsInt($newerIndex, "Record baru tidak ditemukan pada {$routeName}.");
            $this->assertLessThan($olderIndex, $newerIndex, "{$routeName} tidak menampilkan data terbaru lebih dahulu.");
        }
    }
}
