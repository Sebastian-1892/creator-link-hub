<?php

namespace App\Services;

use App\Models\Profile;
use Illuminate\Support\Str;

class SlugService
{
    public function isReserved(string $slug): bool
    {
        $slug = Str::lower($slug);

        return in_array($slug, config('creator.reserved_slugs', []), true);
    }

    public function makeUniqueFromBase(string $base): string
    {
        $base = Str::slug(Str::limit($base, 60, '')) ?: 'creator';
        $slug = $base;
        $i = 1;

        while ($this->isReserved($slug) || Profile::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
