<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');
        $products = Product::active()->with('category')->when($category, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $category)))->orderBy('sort_order')->paginate(12)->withQueryString();
        $categories = ProductCategory::where('is_active', true)->orderBy('sort_order')->get();

        return view('products.index', compact('products', 'categories', 'category'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);
        $product->load('category', 'images');
        $message = urlencode('Halo CiptaOffice, saya ingin menanyakan ketersediaan '.$product->name.'. Jika produk ini tidak tersedia, mohon rekomendasikan alternatif berkualitas setara.');
        $whatsapp = 'https://wa.me/'.preg_replace('/\D/', '', config('ciptaoffice.whatsapp_number')).'?text='.$message;

        return view('products.show', compact('product', 'whatsapp'));
    }
}
