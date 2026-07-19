<?php

namespace App\Models;

use App\Domain\Shared\Naira;
use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'agent_id', 'title', 'slug', 'property_type', 'listing_purpose', 'state', 'city', 'area',
    'display_address', 'description', 'annual_rent', 'bedrooms', 'bathrooms', 'toilets',
    'parking_spaces', 'size_sqm', 'furnishing_status', 'availability_status',
    'publication_status', 'featured', 'published_at',
])]
class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'published_at' => 'datetime',
            'size_sqm' => 'decimal:2',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return $query
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->published();
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order');
    }

    public function amenities(): HasMany
    {
        return $this->hasMany(PropertyAmenity::class)->orderBy('amenity_label');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('publication_status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function getFormattedRentAttribute(): string
    {
        return Naira::annual($this->annual_rent);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->property_type) {
            'self-contain' => 'Self Contain',
            'shared-flat' => 'Shared Flat',
            default => str($this->property_type)->headline()->toString(),
        };
    }
}
