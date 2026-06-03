<div
    class="clh-page-section relative group"
    data-section-key="{{ $section->section_key }}"
    data-page="{{ $section->page }}"
    id="clh-section-{{ $section->section_key }}"
>
    {!! $section->sanitizedContent() !!}

    @if (($clhInlineEditing ?? false) && $section->isEditable())
        <button
            type="button"
            class="absolute top-4 right-4 z-20 inline-flex items-center gap-1 rounded-full bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-lg opacity-0 group-hover:opacity-100 focus:opacity-100 transition"
            onclick="window.Livewire && Livewire.dispatch('open-page-section-editor', { page: @js($section->page), sectionKey: @js($section->section_key), locale: @js($section->locale), label: @js($section->label) })"
        >
            ✎ {{ __('Section bearbeiten') }}
        </button>
        <div class="pointer-events-none absolute inset-0 z-10 rounded-lg ring-2 ring-indigo-400/0 group-hover:ring-indigo-400/70 transition" aria-hidden="true"></div>
    @endif
</div>
