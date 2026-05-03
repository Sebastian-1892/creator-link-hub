<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Link extends Model
{
    protected $fillable = [
        'profile_id',
        'title',
        'url',
        'position',
        'is_active',
        'opens_in_new_tab',
        'tracking_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'opens_in_new_tab' => 'boolean',
            'tracking_enabled' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Profile, $this>
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * @return HasMany<ClickEvent, $this>
     */
    public function clickEvents(): HasMany
    {
        return $this->hasMany(ClickEvent::class);
    }
}
