<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addMinutes(30), function(): string {
            return view('sitemap', [
                'posts' => Post::published()
                    ->select(['id', 'slug', 'updated_at'])
                    ->latest('updated_at')
                    ->get(),
                'products' => Product::active()
                    ->select(['id', 'slug', 'updated_at'])
                    ->latest('updated_at')
                    ->get()
            ])->render();
        });

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }
}
