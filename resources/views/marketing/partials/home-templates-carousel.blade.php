@php
    $carouselThemes = $carouselThemes ?? collect();
    $linkLabel = brand('marketing.all_templates_link');
@endphp

<section class="clh-page-section-blade pb-16 bg-[color:var(--brand-bg-alt)]" data-section-key="templates_carousel" data-page="home">
    <div class="max-w-6xl mx-auto px-4 -mt-4 pb-8">
        <div class="flex gap-5 overflow-x-auto pb-4 snap-x snap-mandatory scrollbar-thin">
            @foreach ($carouselThemes as $theme)
                @php
                    $v = is_array($theme->variables) ? $theme->variables : [];
                    $bg = $v['bg'] ?? '#f8fafc';
                    $text = $v['text'] ?? '#0f172a';
                    $accent = $v['accent'] ?? '#2563eb';
                    $card = $v['card'] ?? '#ffffff';
                @endphp
                <div class="min-w-[220px] snap-start rounded-2xl border shadow-md bg-white overflow-hidden border-[color:var(--brand-border)]">
                    <div class="px-3 py-2 text-[11px] font-semibold truncate" style="background: {{ $bg }}; color: {{ $text }};">{{ $theme->name }}</div>
                    <div class="p-3 space-y-2" style="background: {{ $bg }};">
                        <div class="mx-auto h-8 w-8 rounded-full border-2 shrink-0" style="border-color: {{ $accent }};"></div>
                        @foreach (range(1, 3) as $i)
                            <div class="h-7 rounded-lg text-[10px] font-semibold leading-7 text-center truncate px-1" style="background: {{ $card }}; color: {{ $accent }};">Btn {{ $i }}</div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-center mt-6">
            <a href="{{ route('register') }}" class="text-sm font-semibold underline-offset-4 hover:underline text-[color:var(--brand-accent)]">{{ $linkLabel }} →</a>
        </div>
    </div>
</section>
