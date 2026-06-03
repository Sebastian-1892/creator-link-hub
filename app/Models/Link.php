<?php

namespace App\Models;

use App\Support\LinkPresetHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Link extends Model
{
    protected $fillable = [
        'profile_id',
        'link_type',
        'parent_link_id',
        'title',
        'url',
        'image_url',
        'preset_key',
        'provider',
        'provider_id',
        'provider_resource_type',
        'is_dynamic',
        'cached_title',
        'cached_artist',
        'cached_image',
        'show_icon',
        'position',
        'is_active',
        'opens_in_new_tab',
        'tracking_enabled',
    ];

    protected function casts(): array
    {
        return [
            'parent_link_id' => 'integer',
            'is_dynamic' => 'boolean',
            'show_icon' => 'boolean',
            'is_active' => 'boolean',
            'opens_in_new_tab' => 'boolean',
            'tracking_enabled' => 'boolean',
        ];
    }

    public function isSpotifyEmbed(): bool
    {
        return $this->preset_key === 'spotify'
            && $this->provider === 'spotify'
            && is_string($this->provider_id)
            && $this->provider_id !== '';
    }

    public function spotifyEmbedUrl(): ?string
    {
        if (! $this->isSpotifyEmbed() || ! is_string($this->provider_resource_type)) {
            return null;
        }

        return 'https://open.spotify.com/embed/'.$this->provider_resource_type.'/'.$this->provider_id;
    }

    public function spotifyEmbedHeight(): int
    {
        return in_array($this->provider_resource_type, ['episode', 'show'], true) ? 232 : 152;
    }

    public function spotifyDisplayTitle(): string
    {
        if ($this->is_dynamic && is_string($this->cached_title) && $this->cached_title !== '') {
            return $this->cached_title;
        }

        return $this->title;
    }

    public function spotifyDisplaySubtitle(): ?string
    {
        if ($this->is_dynamic && is_string($this->cached_artist) && $this->cached_artist !== '') {
            return $this->cached_artist;
        }

        return null;
    }

    public function isCollection(): bool
    {
        return $this->link_type === 'collection';
    }

    public function isProduct(): bool
    {
        return $this->link_type === 'product';
    }

    public function iconName(): string
    {
        if ($this->isCollection()) {
            return 'shop';
        }

        if ($this->isProduct()) {
            return 'product';
        }

        return LinkPresetHelper::iconName($this->preset_key, $this->url);
    }

    public function brandColor(): string
    {
        return LinkPresetHelper::brandColor($this->preset_key, $this->url);
    }

    /**
     * @return BelongsTo<Profile, $this>
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * @return BelongsTo<Link, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Link::class, 'parent_link_id');
    }

    /**
     * @return HasMany<Link, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Link::class, 'parent_link_id')->orderBy('position');
    }

    /**
     * @return HasMany<ClickEvent, $this>
     */
    public function clickEvents(): HasMany
    {
        return $this->hasMany(ClickEvent::class);
    }
}
