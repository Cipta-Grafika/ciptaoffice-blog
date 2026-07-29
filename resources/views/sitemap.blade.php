{!! '<' . '?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @php
        $staticPages = [
            ['url' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => route('about'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => route('products.index'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['url' => route('articles.index'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['url' => route('contact.create'), 'priority' => '0.8', 'changefreq' => 'monthly'],
        ];
    @endphp
    @foreach ($staticPages as $page)
        <url>
            <loc>{{ $page['url'] }}</loc>
            <lastmod>{{ now()->startOfDay()->toAtomString() }}</lastmod>
            <changefreq>{{ $page['changefreq'] }}</changefreq>
            <priority>{{ $page['priority'] }}</priority>
        </url>
    @endforeach
    @foreach ($posts as $post)
        <url>
            <loc>{{ route('articles.show', $post) }}</loc>
            <lastmod>{{ $post->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach
    @foreach ($products as $product)
        <url>
            <loc>{{ route('products.show', $product) }}</loc>
            <lastmod>{{ $product->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach
</urlset>
