@php echo '<?xml version="1.0" encoding="UTF-8"?>'; @endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ route('home') }}</loc></url>
    <url><loc>{{ route('properties.index') }}</loc></url>
    @foreach($properties as $property)
        <url><loc>{{ route('properties.show', $property) }}</loc><lastmod>{{ $property->updated_at->toAtomString() }}</lastmod></url>
    @endforeach
</urlset>
