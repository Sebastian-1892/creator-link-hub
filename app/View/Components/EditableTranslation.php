<?php

namespace App\View\Components;

use App\Models\TranslationString;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class EditableTranslation extends Component
{
    public function __construct(
        public string $translationKey,
        public string $value,
        public string $tag = 'span',
        public string $format = TranslationString::FORMAT_TEXT,
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.editable-translation');
    }
}
