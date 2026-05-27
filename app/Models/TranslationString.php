<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TranslationString extends Model
{
    public const UPDATED_AT = 'updated_at';

    public const CREATED_AT = null;

    public const FORMAT_TEXT = 'text';

    public const FORMAT_MARKDOWN = 'markdown';

    public const FORMAT_HTML = 'html';

    protected $fillable = [
        'locale',
        'key',
        'value',
        'format',
        'previous_value',
        'updated_by_user_id',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
