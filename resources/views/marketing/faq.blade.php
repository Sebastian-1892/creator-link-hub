@extends('layouts.marketing')

@section('title', 'FAQ')

@section('content')
    @php
        $faq = $branding['faq'];
    @endphp
    <div class="max-w-3xl mx-auto px-4 py-16 lg:py-20">
        <div class="text-center mb-12">
            <p class="text-sm font-semibold uppercase tracking-wider" style="color: var(--brand-accent);">FAQ</p>
            <h1 class="mt-3 text-3xl sm:text-4xl font-bold" style="color: var(--brand-text);">{{ $faq['title'] }}</h1>
        </div>
        <div class="space-y-4">
            @foreach ($faq['items'] as $item)
                <details
                    class="rounded-2xl border px-5 py-4 bg-white open:shadow-md transition shadow-sm"
                    style="border-color: var(--brand-border);"
                >
                    <summary class="font-semibold text-lg cursor-pointer list-none flex justify-between gap-4" style="color: var(--brand-text);">
                        <span>{{ $item['question'] ?? '' }}</span>
                        <span class="text-xl leading-none opacity-60" aria-hidden="true">+</span>
                    </summary>
                    <p class="mt-4 text-sm leading-relaxed pl-0 border-t pt-4" style="color: var(--brand-text-muted); border-color: var(--brand-border);">
                        {{ $item['answer'] ?? '' }}
                    </p>
                </details>
            @endforeach
        </div>
    </div>
@endsection
