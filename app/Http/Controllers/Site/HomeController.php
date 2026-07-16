<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\HomepageSetting;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Testimonial;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'settings' => HomepageSetting::current(),
            'posts' => Post::published()->with('author')->latest('published_at')->limit(3)->get(),
            'testimonials' => Testimonial::active()->orderBy('sort_order')->limit(3)->get(),
            'categories' => ProductCategory::where('is_active', true)->orderBy('sort_order')->limit(3)->get(),
            'featuredProducts' => Product::active()->where('is_featured', true)->with('category')->orderBy('sort_order')->limit(3)->get(),
        ]);
    }
}
