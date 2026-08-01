<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with('category')->orderBy('sort_order');
        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        if ($request->ajax()) {
            return view('cms.products.partials.table', ['products' => $query->paginate(15)->withQueryString()]);
        }

        return view('cms.products.index', ['products' => $query->paginate(15)->withQueryString()]);
    }

    public function create(): View
    {
        return view('cms.products.form', ['product' => new Product, 'categories' => ProductCategory::orderBy('name')->get()]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $data = $this->data($request);
            $data['slug'] = $this->uniqueSlug($data['name']);
            $product = Product::create($data);

            $this->replaceImages($request, $product);
        });

        return redirect()->route('cms.products.index')->with('success', 'Produk ditambahkan.');
    }

    public function edit(Product $product): View
    {
        $product->load('images');

        return view('cms.products.form', ['product' => $product, 'categories' => ProductCategory::orderBy('name')->get()]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $oldPaths = [];

        DB::transaction(function () use ($request, $product, &$oldPaths): void {
            $product->update($this->data($request));
            if ($product->cover_image_path) {
                $product->update(['cover_image_alt' => $product->name]);
            }
            if (! $request->hasFile('product_images')) {
                $this->applyImageOrder($request, $product);
            }
            [, $oldPaths] = $this->replaceImages($request, $product);
        });

        Storage::disk('public')->delete($oldPaths);

        return redirect()->route('cms.products.index')->with('success', 'Produk diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return back()->with('success', 'Produk dipindahkan ke arsip.');
    }

    public function destroyThumbnail(Product $product): RedirectResponse
    {
        [$deletedPath, $promoted] = DB::transaction(function () use ($product): array {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->getKey());

            abort_if($lockedProduct->cover_image_path === null, 404);

            $deletedPath = $lockedProduct->cover_image_path;
            $nextImage = $lockedProduct->images()->lockForUpdate()->first();

            $lockedProduct->update([
                'cover_image_path' => $nextImage?->path,
                'cover_image_alt' => $nextImage ? $lockedProduct->name : null,
            ]);

            if ($nextImage) {
                $nextImage->delete();
                $this->resequenceImages($lockedProduct);
            }

            return [$deletedPath, $nextImage !== null];
        });

        Storage::disk('public')->delete($deletedPath);

        $message = $promoted
            ? 'Thumbnail dihapus. Gambar carousel pertama dijadikan thumbnail baru.'
            : 'Thumbnail produk dihapus.';

        return redirect()->route('cms.products.edit', $product)->with('success', $message);
    }

    public function destroyImage(Product $product, ProductImage $image): RedirectResponse
    {
        $deletedPath = DB::transaction(function () use ($product, $image): string {
            Product::query()->lockForUpdate()->findOrFail($product->getKey());

            $lockedImage = ProductImage::query()
                ->whereBelongsTo($product)
                ->whereKey($image->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $path = $lockedImage->path;
            $lockedImage->delete();
            $this->resequenceImages($product);

            return $path;
        });

        Storage::disk('public')->delete($deletedPath);

        return redirect()->route('cms.products.edit', $product)
            ->with('success', 'Gambar carousel dihapus.');
    }

    private function data(ProductRequest $request): array
    {
        $data = $request->safe()->except(['product_images', 'gallery_order', 'specifications_text']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');
        $spec = [];
        foreach (preg_split("/\r\n|\r|\n/", (string) $request->input('specifications_text')) as $line) {
            if (str_contains($line, ':')) {
                [$key, $value] = array_map('trim', explode(':', $line, 2));
                if ($key && $value) {
                    $spec[$key] = $value;
                }
            }
        }
        $data['specifications'] = $spec;

        return $data;
    }

    private function replaceImages(ProductRequest $request, Product $product): array
    {
        $files = $request->file('product_images', []);
        if ($files === []) {
            return [[], []];
        }

        $disk = Storage::disk('public');
        $oldPaths = array_values(array_filter([
            $product->cover_image_path,
            ...$product->images()->pluck('path')->all(),
        ]));
        $newPaths = [];

        try {
            foreach ($files as $file) {
                $newPaths[] = $file->store('products/'.$product->getKey(), 'public');
            }

            $product->update([
                'cover_image_path' => $newPaths[0],
                'cover_image_alt' => $product->name,
            ]);
            $product->images()->delete();

            foreach (array_slice($newPaths, 1) as $index => $path) {
                $product->images()->create([
                    'path' => $path,
                    'alt_text' => Str::limit($product->name.' — gambar '.($index + 2), 180, ''),
                    'sort_order' => $index + 1,
                ]);
            }
        } catch (Throwable $exception) {
            $disk->delete($newPaths);

            throw $exception;
        }

        return [$newPaths, $oldPaths];
    }

    private function resequenceImages(Product $product): void
    {
        $product->images()->get()->each(function (ProductImage $image, int $index) use ($product): void {
            $image->update([
                'sort_order' => $index + 1,
                'alt_text' => Str::limit($product->name.' — gambar '.($index + 2), 180, ''),
            ]);
        });
    }

    private function applyImageOrder(ProductRequest $request, Product $product): void
    {
        $order = $request->validated('gallery_order', []);
        if ($order === [] || $product->cover_image_path === null) {
            return;
        }

        $images = $product->images()->lockForUpdate()->get();
        $imagesByToken = $images->keyBy(fn (ProductImage $image): string => 'image:'.$image->getKey());
        $expected = ['thumbnail', ...$imagesByToken->keys()->all()];
        $submitted = array_values($order);
        $sortedExpected = $expected;
        $sortedSubmitted = $submitted;
        sort($sortedExpected);
        sort($sortedSubmitted);

        if ($sortedSubmitted !== $sortedExpected) {
            throw ValidationException::withMessages([
                'gallery_order' => 'Urutan galeri tidak valid. Muat ulang halaman dan coba kembali.',
            ]);
        }

        if ($submitted[0] === 'thumbnail') {
            foreach (array_slice($submitted, 1) as $index => $token) {
                $imagesByToken[$token]->update([
                    'sort_order' => $index + 1,
                    'alt_text' => Str::limit($product->name.' — gambar '.($index + 2), 180, ''),
                ]);
            }

            return;
        }

        $oldThumbnailPath = $product->cover_image_path;
        $newThumbnail = $imagesByToken[$submitted[0]];

        $product->update([
            'cover_image_path' => $newThumbnail->path,
            'cover_image_alt' => $product->name,
        ]);
        $newThumbnail->delete();

        foreach (array_slice($submitted, 1) as $index => $token) {
            $attributes = [
                'sort_order' => $index + 1,
                'alt_text' => Str::limit($product->name.' — gambar '.($index + 2), 180, ''),
            ];

            if ($token === 'thumbnail') {
                $product->images()->create(['path' => $oldThumbnailPath, ...$attributes]);
            } else {
                $imagesByToken[$token]->update($attributes);
            }
        }
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
