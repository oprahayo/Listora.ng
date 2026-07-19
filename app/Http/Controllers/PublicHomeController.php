<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    public function __invoke(): View
    {
        $featured = Property::query()
            ->published()
            ->featured()
            ->with(['agent:id,display_name,verification_status,public_slug', 'images', 'amenities'])
            ->orderByDesc('published_at')
            ->limit(8)
            ->get();

        return view('public.home', compact('featured'));
    }
}
