@props(['class' => ''])
<{{ $tag }} {{ $attributes->merge(['class' => trim($class.' relative inline-block group')]) }}>
    {{ $value }}
    @if ($clhInlineEditing ?? false)
        <button
            type="button"
            class="absolute -top-2 -right-2 z-10 inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-600 text-white text-xs shadow ring-2 ring-white"
            title="{{ __('Bearbeiten') }}"
            onclick="window.Livewire && Livewire.dispatch('open-translation-editor', { key: @js($translationKey), current: @js($value), format: @js($format) })"
        >✎</button>
    @endif
</{{ $tag }}>
