<?php

namespace App\Models;

use App\Services\HtmlSanitizerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSection extends Model
{
    public const UPDATED_AT = 'updated_at';

    public const CREATED_AT = null;

    protected $fillable = [
        'page',
        'section_key',
        'locale',
        'content',
        'previous_content',
        'sort_order',
        'is_visible',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
            'updated_at' => 'datetime',
        ];
    }

    public string $label = '';

    public string $render_type = 'html';

    public ?string $blade_partial = null;

    /**
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function sanitizedContent(): string
    {
        return app(HtmlSanitizerService::class)->sanitize((string) ($this->content ?? ''));
    }

    public function isEditable(): bool
    {
        return $this->render_type === 'html';
    }
}
