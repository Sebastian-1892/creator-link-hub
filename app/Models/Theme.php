<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'variables',
        'preview_image_path',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
        ];
    }

    /**
     * @return HasMany<Profile, $this>
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }
}
