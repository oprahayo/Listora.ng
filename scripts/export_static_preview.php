<?php

declare(strict_types=1);

use App\Models\Property;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$projectRoot = dirname(__DIR__);
$output = $projectRoot.'/site';
$publicBase = 'https://oprahayo.github.io/Listora.ng';

if (File::exists($output)) {
    File::deleteDirectory($output);
}

File::ensureDirectoryExists($output);
File::copyDirectory($projectRoot.'/public/build', $output.'/build');
File::copyDirectory($projectRoot.'/public/images', $output.'/images');
File::copy($projectRoot.'/public/favicon.ico', $output.'/favicon.ico');
File::copy($projectRoot.'/public/service-worker.js', $output.'/service-worker.js');

$manifest = str_replace('"/images/', '"/Listora.ng/images/', File::get($projectRoot.'/public/manifest.webmanifest'));
File::put($output.'/manifest.webmanifest', $manifest);
File::put($output.'/.nojekyll', '');

function renderStaticPage(Kernel $kernel, string $path): string
{
    $request = Request::create($path, 'GET', [], [], [], [
        'HTTP_HOST' => 'localhost:8000',
        'HTTPS' => 'off',
    ]);
    $response = $kernel->handle($request);
    $content = (string) $response->getContent();
    $kernel->terminate($request, $response);

    if ($response->getStatusCode() !== 200) {
        throw new RuntimeException("Static export failed for {$path}: HTTP {$response->getStatusCode()}");
    }

    return $content;
}

function rewriteForPages(string $html, string $publicBase): string
{
    $html = str_replace(
        [
            'http://localhost:8000',
            'http://127.0.0.1:8000',
            'http://localhost',
            'http:\/\/localhost:8000',
            'http:\/\/127.0.0.1:8000',
        ],
        $publicBase,
        $html,
    );

    $html = str_replace(
        ['src="/images/', 'srcset="/images/', 'href="/images/', 'href="/"'],
        [
            'src="'.$publicBase.'/images/',
            'srcset="'.$publicBase.'/images/',
            'href="'.$publicBase.'/images/',
            'href="'.$publicBase.'/"',
        ],
        $html,
    );

    return str_replace(
        [$publicBase.'/properties?page=1', $publicBase.'/properties?page=2'],
        [$publicBase.'/properties/', $publicBase.'/properties/page-2/'],
        $html,
    );
}

function writePage(string $output, string $relativePath, string $html): void
{
    $directory = $output.'/'.trim($relativePath, '/');
    File::ensureDirectoryExists($directory);
    File::put($directory.'/index.html', $html);
}

$pages = [
    '/' => '',
    '/properties' => 'properties',
    '/properties?page=2' => 'properties/page-2',
    '/saved' => 'saved',
    '/join' => 'join',
    '/forgot-password' => 'forgot-password',
    '/offline' => 'offline',
];

foreach ($pages as $route => $directory) {
    writePage($output, $directory, rewriteForPages(renderStaticPage($kernel, $route), $publicBase));
}

$properties = Property::query()
    ->published()
    ->with(['agent:id,display_name,verification_status', 'images', 'amenities'])
    ->orderByDesc('published_at')
    ->get();

foreach ($properties as $property) {
    $route = '/properties/'.$property->slug;
    writePage($output, $route, rewriteForPages(renderStaticPage($kernel, $route), $publicBase));
}

$summaries = $properties->map(function (Property $property) use ($publicBase): array {
    $cover = $property->images->firstWhere('is_cover', true) ?? $property->images->first();
    $image = $cover?->thumbnail_path ?? '/images/properties/apartment-1-thumb.webp';
    $version = @filemtime(public_path(ltrim($image, '/'))) ?: 1;

    return [
        'id' => $property->id,
        'title' => $property->title,
        'rent' => $property->formatted_rent,
        'location' => $property->area.', '.$property->city,
        'url' => $publicBase.'/properties/'.$property->slug.'/',
        'image' => $publicBase.$image.'?v='.$version,
        'image_alt' => $cover?->alt_text ?? $property->title,
        'verified' => $property->agent->isVerified(),
        'facts' => collect([
            $property->bedrooms ? $property->bedrooms.' bed' : null,
            $property->bathrooms ? $property->bathrooms.' bath' : null,
        ])->filter()->values(),
    ];
});

File::ensureDirectoryExists($output.'/data');
File::put(
    $output.'/data/properties.json',
    json_encode(['properties' => $summaries], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
);

echo 'Exported '.count($pages).' public pages and '.$properties->count()." property details to site/.\n";
