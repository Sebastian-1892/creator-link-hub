@extends('layouts.marketing')

@section('title', __('Hilfe'))

@section('content')
    @php
        $help = $branding['help'];
    @endphp
    <div class="max-w-3xl mx-auto px-4 py-16 lg:py-20">
        <div class="text-center mb-10">
            <p class="text-sm font-semibold uppercase tracking-wider" style="color: var(--brand-accent);">{{ __('Support') }}</p>
            <h1 class="mt-3 text-3xl sm:text-4xl font-bold" style="color: var(--brand-text);">{{ $help['title'] }}</h1>
            <p class="mt-4 text-base max-w-xl mx-auto" style="color: var(--brand-text-muted);">{{ $help['intro'] }}</p>
        </div>
        <div class="space-y-6">
            @foreach ($help['sections'] as $section)
                <div
                    class="rounded-2xl border p-8 bg-white shadow-sm"
                    style="border-color: var(--brand-border); color: var(--brand-text-muted);"
                >
                    <h2 class="text-lg font-semibold" style="color: var(--brand-text);">{{ $section['heading'] ?? '' }}</h2>
                    <p class="mt-3 leading-relaxed whitespace-pre-line">{{ $section['body'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
@endsection
