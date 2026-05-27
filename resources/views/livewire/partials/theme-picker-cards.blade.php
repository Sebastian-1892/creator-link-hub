<div class="max-h-[min(24rem,50vh)] overflow-y-auto rounded-lg border border-gray-200 bg-gray-50/50 p-3" role="radiogroup" aria-label="{{ __('Profil-Vorlage') }}">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-2 2xl:grid-cols-3">
        @foreach ($themes as $theme)
            @php
                $v = is_array($theme->variables) ? $theme->variables : [];
                $bg = $v['bg'] ?? '#e5e7eb';
                $text = $v['text'] ?? '#111827';
                $accent = $v['accent'] ?? '#6366f1';
                $card = $v['card'] ?? '#f3f4f6';
                $btnRadius = match ($theme->button_style ?? 'pill') {
                    'square' => '4px',
                    'rounded', 'glass', 'shadow' => '12px',
                    default => '9999px',
                };
                $glass = ($theme->button_style ?? '') === 'glass';
                $isSelected = filled($theme_id) && (int) $theme_id === (int) $theme->id;
            @endphp
            <label @class([
                'flex cursor-pointer flex-col rounded-xl border-2 bg-white p-3 shadow-sm transition min-h-[8.5rem]',
                'border-indigo-600 ring-2 ring-indigo-100' => $isSelected,
                'border-gray-200 hover:border-gray-300' => ! $isSelected,
            ])>
                <input
                    type="radio"
                    wire:model.live="theme_id"
                    value="{{ $theme->id }}"
                    class="sr-only"
                />
                <span class="text-xs font-semibold text-gray-900 truncate">{{ $theme->name }}</span>
                <div class="mt-2 flex-1 rounded-lg border border-black/10 overflow-hidden" style="background: {{ $bg }};">
                    <div class="p-2 flex flex-col items-center gap-1.5">
                        <div class="h-7 w-7 rounded-full border-2 shrink-0" style="border-color: {{ $accent }};"></div>
                        <div class="h-1 w-16 rounded-full opacity-40" style="background: {{ $text }};"></div>
                        @foreach (range(1, 3) as $i)
                            <div
                                class="w-full h-6 text-[9px] font-bold flex items-center justify-center px-1 truncate"
                                style="
                                    background: {{ $glass ? 'rgba(255,255,255,0.12)' : $card }};
                                    color: {{ $accent }};
                                    border-radius: {{ $btnRadius }};
                                    border: 1px solid {{ $glass ? 'rgba(255,255,255,0.2)' : 'rgba(0,0,0,0.06)' }};
                                    {{ $glass ? 'backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);' : '' }}
                                "
                            >•••</div>
                        @endforeach
                    </div>
                </div>
            </label>
        @endforeach
    </div>
</div>
