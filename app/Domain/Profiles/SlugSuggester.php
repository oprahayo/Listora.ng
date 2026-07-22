<?php

namespace App\Domain\Profiles;

use App\Models\AgentProfile;
use Illuminate\Support\Str;

class SlugSuggester
{
    public function unique(string $displayName, ?AgentProfile $except = null): string
    {
        $base = Str::slug($displayName) ?: 'property-professional';
        $slug = $base;
        $suffix = 2;

        while (AgentProfile::query()
            ->when($except, fn ($query) => $query->whereKeyNot($except->getKey()))
            ->where('public_slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
