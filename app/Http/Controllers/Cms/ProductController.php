<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('cms.products.index', ['products' => Product::with('category')->orderBy('sort_order')->paginate(15)]);
    }

    public function create(): View
    {
        return view('cms.products.form', ['product' => new Product, 'categories' => ProductCategory::orderBy('name')->get()]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $this->data($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        Product::create($data);

        return redirect()->route('cms.products.index')->with('success', 'Produk ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('cms.products.form', ['product' => $product, 'categories' => ProductCategory::orderBy('name')->get()]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($this->data($request, $product));

        return redirect()->route('cms.products.index')->with('success', 'Produk diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return back()->with('success', 'Produk dipindahkan ke arsip.');
    }

    private function data(ProductRequest $request, ?Product $product = null): array
    {
        $data = $request->safe()->except(['cover_image', 'specifications_text']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');
        $spec = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) $request->input('specifications_text')) as $line) {
            if (str_contains($line, ':')) {
                [$key,$value] = array_map('trim', explode(':', $line, 2));
                if ($key && $value) {
                    $spec[$key] = $value;
                }
            }
        } $data['specifications'] = $spec;
        if ($request->hasFile('cover_image')) {
            if ($product?->cover_image_path) {
                Storage::disk('public')->delete($product->cover_image_path);
            } $data['cover_image_path'] = $request->file('cover_image')->store('products', 'public');
        }

        return $data;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: Str::random(8);
        $slug = $base;
        $i = 2;
        while (Product::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

return $slug;
    }
}
