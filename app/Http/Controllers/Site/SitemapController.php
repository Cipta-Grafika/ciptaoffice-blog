<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        return response()->view('sitemap', ['posts' => Post::published()->get(), 'products' => Product::active()->get()])->header('Content-Type', 'application/xml');
    }
}
