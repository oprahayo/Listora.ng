<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $properties = Property::query()->published()->select(['slug', 'updated_at'])->get();

        return response()
            ->view('public.sitemap', compact('properties'))
            ->header('Content-Type', 'application/xml');
    }
}
