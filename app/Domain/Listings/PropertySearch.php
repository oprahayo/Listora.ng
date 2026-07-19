<?php

namespace App\Domain\Listings;

use App\Models\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class PropertySearch
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = Property::query()
            ->published()
            ->with(['agent:id,display_name,verification_status,public_slug', 'images', 'amenities']);

        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters['sort'] ?? 'latest');

        return $query->paginate(12)->withQueryString();
    }

    /** @param array<string, mixed> $filters */
    private function applyFilters(Builder $query, array $filters): void
    {
        if ($q = $filters['q'] ?? null) {
            $query->where(function (Builder $inner) use ($q): void {
                $inner->where('title', 'like', "%{$q}%")
                    ->orWhere('area', 'like', "%{$q}%")
                    ->orWhere('city', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        foreach (['state', 'city', 'area'] as $field) {
            if ($value = $filters[$field] ?? null) {
                $query->where($field, $value);
            }
        }

        if ($type = $filters['type'] ?? null) {
            $query->where('property_type', $type);
        }

        if (isset($filters['min_price'])) {
            $query->where('annual_rent', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('annual_rent', '<=', $filters['max_price']);
        }

        if (isset($filters['bedrooms'])) {
            $query->where('bedrooms', '>=', $filters['bedrooms']);
        }

        if ($furnishing = $filters['furnishing'] ?? null) {
            $query->where('furnishing_status', $furnishing);
        }

        foreach ($filters['amenities'] ?? [] as $amenity) {
            $query->whereHas('amenities', fn (Builder $amenityQuery) => $amenityQuery->where('amenity_key', $amenity));
        }
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'price_asc' => $query->orderBy('annual_rent'),
            'price_desc' => $query->orderByDesc('annual_rent'),
            default => $query->orderByDesc('published_at'),
        };
    }
}
