{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach([route('home'),route('about'),route('products.index'),route('articles.index'),route('contact.create')] as $url)<url><loc>{{ $url }}</loc></url>@endforeach
@foreach($posts as $post)<url><loc>{{ route('articles.show',$post) }}</loc><lastmod>{{ $post->updated_at->toAtomString() }}</lastmod></url>@endforeach
@foreach($products as $product)<url><loc>{{ route('products.show',$product) }}</loc><lastmod>{{ $product->updated_at->toAtomString() }}</lastmod></url>@endforeach
</urlset>
