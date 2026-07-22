<?php

use App\Http\Controllers\Auth\DashboardController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Auth\WorkspaceController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\PublicCachePolicy;
use Illuminate\Support\Facades\Route;

Route::middleware(PublicCachePolicy::class)->group(function (): void {
    Route::get('/', PublicHomeController::class)->name('home');
    Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
    Route::get('/properties/{property:slug}', [PropertyController::class, 'show'])->name('properties.show');
    Route::get('/saved', [PropertyController::class, 'saved'])->name('saved');
});
Route::get('/saved/property-summaries', [PropertyController::class, 'savedSummaries'])->name('saved.summaries');

Route::post('/auth/login', [SessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
Route::post('/auth/otp/request', [OtpController::class, 'store'])->middleware('throttle:otp')->name('otp.request');
Route::post('/auth/logout', [SessionController::class, 'destroy'])->middleware('auth')->name('logout');
Route::get('/auth/csrf-token', fn () => response()->json(['token' => csrf_token()]))->name('csrf.refresh');

Route::view('/join', 'public.join')->name('join');
Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
Route::view('/offline', 'public.offline')->name('offline');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/manifest.webmanifest', fn () => response()->file(public_path('manifest.webmanifest'), [
    'Content-Type' => 'application/manifest+json',
]))->name('manifest');

Route::get('/service-worker.js', fn () => response()->file(public_path('service-worker.js'), [
    'Content-Type' => 'application/javascript',
    'Service-Worker-Allowed' => '/',
]))->name('service-worker');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/workspace', [WorkspaceController::class, 'index'])->name('workspace.index');
    Route::post('/workspace/switch', [WorkspaceController::class, 'switch'])->name('workspace.switch');

    Route::middleware('role:agent')->group(function (): void {
        Route::view('/agent/dashboard', 'auth.dashboard', ['role' => 'agent'])->name('agent.dashboard');
        Route::view('/agent/properties', 'auth.dashboard', ['role' => 'agent', 'context' => 'properties'])->name('agent.properties.index');
    });
    Route::view('/landlord/dashboard', 'auth.dashboard', ['role' => 'landlord'])->middleware('role:landlord')->name('landlord.dashboard');
    Route::view('/tenant/dashboard', 'auth.dashboard', ['role' => 'tenant'])->middleware('role:tenant')->name('tenant.dashboard');
    Route::view('/admin/dashboard', 'auth.dashboard', ['role' => 'admin'])->middleware('role:admin')->name('admin.dashboard');
});
