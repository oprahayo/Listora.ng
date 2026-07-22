<?php

declare(strict_types=1);

use App\Models\AgentProfile;
use App\Models\Invitation;
use App\Models\Property;
use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

function renderStaticView(string $viewName, array $data = [], ?User $user = null, ?string $role = null): string
{
    Auth::logout();
    session()->flush();
    if ($user) {
        Auth::login($user);
        session()->put('active_role', $role ?: $user->last_active_role ?: $user->primary_role);
    }
    $html = view($viewName, $data)->render();
    Auth::logout();
    session()->flush();

    return $html;
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

$pendingAgent = User::query()->where('email', 'pending.agent@listora.test')->with('agent')->firstOrFail();
$verifiedAgent = User::query()->where('email', 'adaeze@listora.test')->with('agent')->firstOrFail();
$landlord = User::query()->where('email', 'landlord@listora.test')->with('landlordProfile')->firstOrFail();
$tenant = User::query()->where('email', 'tenant@listora.test')->with('tenantProfile')->firstOrFail();
$admin = User::query()->where('email', 'admin@listora.test')->firstOrFail();
$multi = User::query()->where('email', 'multi@listora.test')->with('roles')->firstOrFail();
$pendingVerification = VerificationRequest::query()->where('user_id', $pendingAgent->id)->with(['documents', 'organization', 'user.agent'])->firstOrFail();

$privatePreviewPages = [
    'verify-phone' => renderStaticView('auth.verify-phone', [], $pendingAgent, 'agent'),
    'onboarding/agent' => renderStaticView('onboarding.agent', ['profile' => $pendingAgent->agent, 'verification' => $pendingVerification, 'step' => 1], $pendingAgent, 'agent'),
    'onboarding/agent/verification' => renderStaticView('onboarding.agent', ['profile' => $pendingAgent->agent, 'verification' => $pendingVerification, 'step' => 3], $pendingAgent, 'agent'),
    'agent/dashboard' => renderStaticView('dashboards.agent', [
        'profile' => $verifiedAgent->agent,
        'verification' => null,
        'invitationCounts' => Invitation::query()->where('invited_by', $verifiedAgent->id)->selectRaw('status, count(*) total')->groupBy('status')->pluck('total', 'status'),
    ], $verifiedAgent, 'agent'),
    'agent/invitations' => renderStaticView('invitations.agent-index', [
        'invitations' => Invitation::query()->where('invited_by', $verifiedAgent->id)->latest()->paginate(15),
    ], $verifiedAgent, 'agent'),
    'landlord/dashboard' => renderStaticView('dashboards.landlord', [
        'profile' => $landlord->landlordProfile,
        'invitations' => Invitation::query()->where('intended_role', 'landlord')->latest()->take(5)->get(),
    ], $landlord, 'landlord'),
    'tenant/dashboard' => renderStaticView('dashboards.tenant', [
        'profile' => $tenant->tenantProfile,
        'invitations' => Invitation::query()->where('intended_role', 'tenant')->latest()->take(5)->get(),
    ], $tenant, 'tenant'),
    'workspace' => renderStaticView('auth.workspace', ['roles' => $multi->roles()->orderBy('display_name')->get()], $multi, 'agent'),
    'admin/verifications' => renderStaticView('admin.verifications.index', [
        'requests' => VerificationRequest::query()->with(['user.agent', 'organization'])->where('status', 'submitted')->latest('submitted_at')->paginate(15),
        'counts' => VerificationRequest::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        'status' => 'submitted',
    ], $admin, 'admin'),
    'admin/verifications/review' => renderStaticView('admin.verifications.show', [
        'verificationRequest' => $pendingVerification,
        'documentUrls' => $pendingVerification->documents->mapWithKeys(fn ($document) => [$document->id => '#']),
    ], $admin, 'admin'),
];

foreach ($privatePreviewPages as $directory => $html) {
    writePage($output, $directory, rewriteForPages($html, $publicBase));
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

AgentProfile::query()->with(['properties' => fn ($query) => $query->published()->with(['images', 'amenities'])->latest('published_at')->take(8)])->get()
    ->each(function (AgentProfile $agent) use ($output, $kernel, $publicBase): void {
        writePage($output, '/agent/'.$agent->public_slug, rewriteForPages(renderStaticPage($kernel, '/agent/'.$agent->public_slug), $publicBase));
    });

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

echo 'Exported '.(count($pages) + count($privatePreviewPages)).' preview pages and '.$properties->count()." property details to site/.\n";
