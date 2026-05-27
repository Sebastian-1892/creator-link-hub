@props(['class' => ''])
<{{ $tag }} {{ $attributes->merge(['class' => trim($class.' relative inline-block group')]) }}>
    {{ $value }}
    @if ($clhInlineEditing ?? false)
        <button
            type="button"
            class="absolute -top-2 -right-2 hidden group-hover:inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 text-white text-xs shadow"
            title="{{ __('Bearbeiten') }}"
            x-data
            @click="$dispatch('open-translation-editor', { key: @js($translationKey), current: @js($value), format: @js($format) })"
        >✎</button>
    @endif
</{{ $tag }}>
