<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Profile extends Model
{
    protected $fillable = [
        'workspace_id',
        'theme_id',
        'slug',
        'display_name',
        'bio',
        'avatar_path',
        'theme_variables',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'theme_variables' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Workspace, $this>
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * @return BelongsTo<Theme, $this>
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    /**
     * @return HasMany<Link, $this>
     */
    public function links(): HasMany
    {
        return $this->hasMany(Link::class)->orderBy('position');
    }

    /**
     * @return HasMany<ClickEvent, $this>
     */
    public function clickEvents(): HasMany
    {
        return $this->hasMany(ClickEvent::class);
    }

    public function cacheTag(): string
    {
        return 'profile:'.$this->slug;
    }

    public static function publicProfileCacheKey(string $slug): string
    {
        return 'pub_profile_v1_'.$slug;
    }

    public static function forgetPublicProfileCacheForProfileId(int $profileId): void
    {
        $slug = static::query()->whereKey($profileId)->value('slug');
        if (is_string($slug) && $slug !== '') {
            Cache::forget(static::publicProfileCacheKey($slug));
        }
    }
}
