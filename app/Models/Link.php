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
            'show_icon' => 'boolean',
            'is_active' => 'boolean',
            'opens_in_new_tab' => 'boolean',
            'tracking_enabled' => 'boolean',
        ];
    }

    public function isCollection(): bool
    {
        return $this->link_type === 'collection';
    }

    public function iconName(): string
    {
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
