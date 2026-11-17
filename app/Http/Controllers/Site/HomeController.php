<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\HomepageSetting;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Menangani rendering halaman beranda publik dengan cache section HTML.
 */
class HomeController extends Controller
{
    /**
     * Menampilkan halaman beranda menggunakan cache HTML partial untuk mencegah serialisasi objek model.
     *
     * @return View Halaman beranda lengkap.
     */
    public function __invoke(): View
    {
        $sectionsHtml = Cache::remember('home.sections', now()->addMinutes(15), function (): string {
            return view('partials.home-sections', [
                'settings' => HomepageSetting::current(),
                'posts' => Post::published()
                    ->select(['id', 'author_id', 'author_name', 'title', 'slug', 'excerpt', 'cover_image_path', 'cover_image_alt', 'published_at'])
                    ->with('author')->latest('published_at')->limit(3)
                    ->get(),
                'testimonials' => Testimonial::active()->orderBy('sort_order')->limit(3)->get(),
                'categories' => ProductCategory::where('is_active', true)->orderBy('sort_order')->limit(3)->get(),
                'featuredProducts' => Product::active()
                    ->select(['id', 'product_category_id', 'name', 'slug', 'summary', 'cover_image_path', 'cover_image_alt', 'sort_order'])
                    ->where('is_featured', true)->with('category')->orderBy('sort_order')->limit(3)
                    ->get(),
            ])->render();
        });

        return view('home', [
            'sectionsHtml' => $sectionsHtml,
        ]);
    }
}
