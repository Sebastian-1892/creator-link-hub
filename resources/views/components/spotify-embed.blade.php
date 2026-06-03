@props([
    'embedUrl',
    'resourceType' => 'track',
    'title' => 'Spotify',
])

@php
    $height = in_array($resourceType, ['episode', 'show'], true) ? 232 : 152;
@endphp

<iframe
    src="{{ $embedUrl }}"
    width="100%"
    height="{{ $height }}"
    frameborder="0"
    allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
    loading="lazy"
    title="{{ $title }}"
    {{ $attributes->merge(['class' => 'block w-full rounded-xl']) }}
></iframe>
