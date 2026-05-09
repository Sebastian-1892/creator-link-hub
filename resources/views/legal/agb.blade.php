@extends('layouts.marketing')

@section('title', 'AGB')

@section('content')
    @php
        $brandSvc = app(\App\Services\BrandingService::class);
        $html = $brandSvc->renderRich($branding['legal']['agb_html'] ?? '');
    @endphp
    <div class="max-w-3xl mx-auto px-4 py-16 prose prose-slate prose-headings:font-bold prose-a:text-[var(--brand-accent)] max-w-none">
        {!! $html !!}
    </div>
@endsection
