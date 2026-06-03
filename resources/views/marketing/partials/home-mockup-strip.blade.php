@php
    $stripThemes = $stripThemes ?? collect();
@endphp

<section class="max-w-6xl mx-auto px-4 pb-12 clh-page-section-blade" data-section-key="mockup_strip" data-page="home">
    <p class="text-center text-sm font-semibold uppercase tracking-wider text-[color:var(--brand-text-muted)]">{{ __('Live-Vorschau') }}</p>
    <div class="mt-8 grid gap-6 sm:grid-cols-3">
        @foreach ($stripThemes as $theme)
            @php
                $v = is_array($theme->variables) ? $theme->variables : [];
                $bg = $v['bg'] ?? '#1e293b';
                $text = $v['text'] ?? '#f8fafc';
                $accent = $v['accent'] ?? '#22d3ee';
                $card = $v['card'] ?? '#334155';
                $btnRadius = match ($theme->button_style ?? 'pill') {
                    'square' => '6px',
                    'rounded', 'glass', 'shadow' => '14px',
                    default => '9999px',
                };
            @endphp
            <div class="rounded-2xl border overflow-hidden shadow-md bg-white border-[color:var(--brand-border)]">
                <div class="px-4 py-3 text-xs font-semibold flex justify-between" style="background: {{ $bg }}; color: {{ $text }};">
                    <span>{{ $theme->name }}</span>
                    <span style="color: {{ $accent }};">●</span>
                </div>
                <div class="p-4 space-y-2" style="background: {{ $bg }};">
                    <div class="mx-auto h-11 w-11 rounded-full border-2" style="border-color: {{ $accent }};"></div>
                    <div class="h-2 rounded-full mx-auto w-1/2 opacity-50" style="background: {{ $text }};"></div>
                    @foreach (range(1, 3) as $i)
                        <div
                            class="h-9 text-center text-xs font-semibold leading-9 px-2 truncate"
                            style="background: {{ $card }}; color: {{ $accent }}; border-radius: {{ $btnRadius }};"
                        >Link {{ $i }}</div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</section>
