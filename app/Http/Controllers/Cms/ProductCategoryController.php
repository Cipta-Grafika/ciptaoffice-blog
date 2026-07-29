<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = ProductCategory::withCount('products')->orderBy('sort_order');
        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        if ($request->ajax()) {
            return view('cms.categories.partials.table', ['categories' => $query->paginate(15)->withQueryString()]);
        }

        return view('cms.categories.index', ['categories' => $query->paginate(15)->withQueryString()]);
    }

    public function create(): View
    {
        return view('cms.categories.form', ['category' => new ProductCategory]);
    }

    public function store(Request $request): RedirectResponse
    {
        ProductCategory::create($this->data($request));

        return redirect()->route('cms.categories.index')->with('success', 'Kategori ditambahkan.');
    }

    public function edit(ProductCategory $category): View
    {
        return view('cms.categories.form', compact('category'));
    }

    public function update(Request $request, ProductCategory $category): RedirectResponse
    {
        $category->update($this->data($request, $category));

        return redirect()->route('cms.categories.index')->with('success', 'Kategori diperbarui.');
    }

    public function destroy(ProductCategory $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['category' => 'Kategori masih memiliki produk.']);
        } $category->delete();

        return back()->with('success', 'Kategori dihapus.');
    }

    private function data(Request $request, ?ProductCategory $category = null): array
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'slug' => ['nullable', 'string', 'max:140', Rule::unique('product_categories')->ignore($category)], 'description' => ['nullable', 'string', 'max:1000'], 'sort_order' => ['required', 'integer', 'min:0'], 'is_active' => ['nullable', 'boolean']]);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
