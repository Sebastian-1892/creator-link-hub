@extends('layouts.marketing')

@section('title', __('Startseite'))

@section('content')
    @foreach ($pageSections as $section)
        @if (! $section->is_visible)
            @continue
        @endif

        @if ($section->render_type === 'blade' && is_string($section->blade_partial))
            @include($section->blade_partial, [
                'stripThemes' => $stripThemes ?? collect(),
                'carouselThemes' => $carouselThemes ?? collect(),
            ])
        @else
            @include('marketing.partials.page-section', ['section' => $section])
        @endif
    @endforeach
@endsection
