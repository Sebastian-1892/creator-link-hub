@extends('layouts.marketing')

@section('title', __('Preise'))

@section('content')
    @php
        $p = $branding['pricing'];
        $plans = $p['plans'];
        $order = ['free', 'starter', 'pro'];
    @endphp
    <div class="max-w-5xl mx-auto px-4 py-16 lg:py-20">
        <div class="text-center max-w-2xl mx-auto">
            <p class="text-sm font-semibold uppercase tracking-wider" style="color: var(--brand-accent);">{{ __('Preise') }}</p>
            <h1 class="mt-3 text-3xl sm:text-4xl font-bold" style="color: var(--brand-text);">{{ $p['title'] }}</h1>
            <p class="mt-4 text-base" style="color: var(--brand-text-muted);">{{ $p['subline'] }}</p>
        </div>
        <div class="mt-14 grid gap-6 md:grid-cols-3">
            @foreach ($order as $key)
                @php
                    $plan = $plans[$key] ?? [];
                    $isStarter = $key === 'starter';
                @endphp
                <div
                    @class([
                        'rounded-2xl border p-7 flex flex-col transition hover:-translate-y-1 bg-white',
                        'border-2 shadow-xl md:scale-[1.02]' => $isStarter,
                        'shadow-sm hover:shadow-lg' => ! $isStarter,
                    ])
                    style="{{ $isStarter ? 'border-color: var(--brand-primary); background: color-mix(in srgb, var(--brand-card) 96%, var(--brand-primary));' : 'border-color: var(--brand-border);' }}"
                >
                    <h2 class="text-lg font-semibold" style="color: {{ $isStarter ? 'var(--brand-accent)' : 'var(--brand-text)' }};">{{ $plan['name'] }}</h2>
                    <p class="mt-3 text-3xl font-bold" style="color: var(--brand-text);">
                        {{ $plan['price'] }}
                        @if (($plan['period'] ?? '') !== '')
                            <span class="text-base font-normal" style="color: var(--brand-text-muted);">/ {{ $plan['period'] }}</span>
                        @endif
                    </p>
                    <ul class="mt-6 space-y-2 text-sm flex-1" style="color: {{ $isStarter ? 'var(--brand-text)' : 'var(--brand-text-muted)' }};">
                        @foreach ($plan['features'] ?? [] as $line)
                            <li>• {{ $line }}</li>
                        @endforeach
                    </ul>
                    <a
                        href="{{ route('register') }}"
                        class="mt-8 block text-center rounded-full py-2.5 text-sm font-semibold transition hover:opacity-95"
                        style="{{ $isStarter ? 'background: var(--brand-primary); color: var(--brand-primary-contrast);' : 'border: 2px solid var(--brand-border); color: var(--brand-text);' }}"
                    >{{ $plan['cta'] }}</a>
                </div>
            @endforeach
        </div>
    </div>
@endsection
