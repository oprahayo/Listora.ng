<?php

namespace App\Http\Controllers;

use App\Domain\Listings\PropertySearch;
use App\Http\Requests\PropertyFilterRequest;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function index(PropertyFilterRequest $request, PropertySearch $search): View
    {
        $filters = $request->validated();
        $properties = $search->search($filters);

        return view('public.properties.index', compact('properties', 'filters'));
    }

    public function show(Property $property): View
    {
        $property->load(['agent:id,display_name,verification_status,public_slug,short_bio,primary_location', 'images', 'amenities']);

        return view('public.properties.show', compact('property'));
    }

    public function saved(): View
    {
        return view('public.saved');
    }

    public function savedSummaries(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['nullable', 'array', 'max:100'],
            'ids.*' => ['integer', 'min:1'],
        ]);

        $ids = collect($validated['ids'] ?? [])->unique()->values();
        $properties = Property::query()
            ->published()
            ->whereIn('id', $ids)
            ->with(['agent:id,display_name,verification_status', 'images', 'amenities'])
            ->get()
            ->sortBy(fn (Property $property) => $ids->search($property->id))
            ->values()
            ->map(fn (Property $property) => [
                'id' => $property->id,
                'title' => $property->title,
                'rent' => $property->formatted_rent,
                'location' => $property->area.', '.$property->city,
                'url' => route('properties.show', $property),
                'image' => $property->images->firstWhere('is_cover', true)?->thumbnail_path
                    ?? $property->images->first()?->thumbnail_path,
                'image_alt' => $property->images->first()?->alt_text ?? $property->title,
                'verified' => $property->agent->isVerified(),
                'facts' => collect([
                    $property->bedrooms ? $property->bedrooms.' bed' : null,
                    $property->bathrooms ? $property->bathrooms.' bath' : null,
                ])->filter()->values(),
            ]);

        return response()->json(['properties' => $properties, 'valid_ids' => $properties->pluck('id')]);
    }
}
