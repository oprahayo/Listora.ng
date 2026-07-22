<?php

use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\SessionController;
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
