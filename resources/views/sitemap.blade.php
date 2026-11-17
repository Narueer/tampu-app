{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($paginas as $p)
    <url>
        <loc>{{ $p['url'] }}</loc>
@if ($p['fecha'])
        <lastmod>{{ $p['fecha']->toAtomString() }}</lastmod>
@endif
    </url>
@endforeach
</urlset>
