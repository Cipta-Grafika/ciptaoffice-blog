<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Menangani tampilan katalog produk dan detail produk publik.
 */
class ProductController extends Controller
{
    /**
     * Menampilkan daftar produk aktif beserta filter kategori (di-cache sebagai array) dan pencarian.
     *
     * @param  Request  $request  Request HTTP yang membawa parameter filter kategori dan kata kunci.
     * @return View Halaman katalog penuh atau partial HTML untuk permintaan AJAX.
     */
    public function index(Request $request): View
    {
        $category = $request->query('category');
        $q = trim((string) $request->query('q'));
        $products = Product::active()
            ->select(['id', 'product_category_id', 'name', 'slug', 'summary', 'cover_image_path', 'cover_image_alt', 'sort_order'])
            ->with('category')
            ->when($category, fn ($query) => $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $category)))
            ->when($q, fn ($query) => $query->where(fn ($sub) => $sub->where('name', 'like', '%'.$q.'%')->orWhere('summary', 'like', '%'.$q.'%')))
            ->orderBy('sort_order')
            ->paginate(12)
            ->withQueryString();
        $categoryRows = Cache::remember('products.active_categories', now()->addHour(), fn (): array =>
            ProductCategory::where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'slug'])->toArray()
        );
        $categories = ProductCategory::hydrate($categoryRows);
        if ($request->ajax()) {
            return view('products.partials.catalog', compact('products', 'categories', 'category', 'q'));
        }

        return view('products.index', compact('products', 'categories', 'category', 'q'));
    }

    /**
     * Menampilkan detail produk aktif beserta galeri dan tautan WhatsApp kontekstual.
     *
     * @param  Product  $product  Instance produk berdasarkan slug.
     * @return View Halaman detail produk.
     */
    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);
        $product->load('category', 'images');
        $message = urlencode('Halo CiptaOffice, saya ingin menanyakan ketersediaan '.$product->name.'. Jika produk ini tidak tersedia, mohon rekomendasikan alternatif berkualitas setara.');
        $whatsapp = 'https://wa.me/'.preg_replace('/\D/', '', config('ciptaoffice.whatsapp_number')).'?text='.$message;

        return view('products.show', compact('product', 'whatsapp'));
    }
}
