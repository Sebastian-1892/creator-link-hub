@extends('layouts.public', ['profile' => $profile, 'showPlatformBranding' => $showPlatformBranding])

@section('content')
    @php
        $clh = clh_public_theme($profile);
    @endphp
    <div class="max-w-md mx-auto px-4 py-12 pb-28">
        <header class="text-center">
            @if ($profile->avatar_path)
                <img
                    src="{{ \Illuminate\Support\Facades\Storage::url($profile->avatar_path) }}"
                    alt=""
                    class="{{ $clh['avatar_class'] }}"
                    style="{{ $clh['avatar_style'] }}"
                >
            @else
                <div
                    class="{{ $clh['placeholder_avatar_class'] }} text-4xl"
                    style="{{ $clh['placeholder_avatar_style'] }}"
                >
                    {{ \Illuminate\Support\Str::substr($profile->display_name, 0, 1) }}
                </div>
            @endif
            <h1 class="mt-6 text-3xl font-bold tracking-tight">{{ $profile->display_name }}</h1>
            @if ($profile->bio)
                <p class="mt-3 text-base leading-relaxed whitespace-pre-line opacity-90" style="color: var(--clh-text-muted);">{{ $profile->bio }}</p>
            @endif
        </header>

        @php
            $topLevelLinks = $profile->links->whereNull('parent_link_id')->values();
            $productsByCollection = $profile->links->whereNotNull('parent_link_id')->groupBy('parent_link_id');
        @endphp

        <div class="mt-12 space-y-3">
            @foreach ($topLevelLinks as $link)
                @php
                    $href = $link->tracking_enabled ? route('links.redirect', $link) : $link->url;
                    $target = $link->opens_in_new_tab ? '_blank' : '_self';
                    $rel = $link->opens_in_new_tab ? 'noopener noreferrer' : null;
                    $brandColor = $link->brandColor();
                    $isCollection = $link->isCollection();
                    $products = $productsByCollection[$link->id] ?? collect();
                @endphp
                @if ($isCollection)
                    <div x-data="{ open: false }" class="space-y-2">
                        <button
                            type="button"
                            @click="open = !open"
                            class="{{ $clh['link_class'] }} w-full text-left shadow-md hover:shadow-xl pl-4 pr-6"
                            style="{{ $clh['link_style'] }}"
                            :aria-expanded="open ? 'true' : 'false'"
                        >
                            <span class="flex-1 text-center">{{ $link->title }}</span>
                            <span class="text-lg transition" :class="open ? 'rotate-90' : ''" style="color: var(--clh-accent);" aria-hidden="true">›</span>
                        </button>

                        <div x-show="open" x-transition class="pl-2 space-y-2">
                            @foreach ($products as $product)
                                @php
                                    $productHref = $product->tracking_enabled ? route('links.redirect', $product) : $product->url;
                                    $productTarget = $product->opens_in_new_tab ? '_blank' : '_self';
                                    $productRel = $product->opens_in_new_tab ? 'noopener noreferrer' : null;
                                @endphp
                                <a
                                    href="{{ $productHref }}"
                                    target="{{ $productTarget }}"
                                    @if ($productRel) rel="{{ $productRel }}" @endif
                                    class="group flex items-center gap-3 rounded-2xl border border-white/40 bg-white/70 px-3 py-2 shadow-sm backdrop-blur-sm"
                                >
                                    <div class="h-12 w-12 rounded-lg overflow-hidden bg-white/40 shrink-0">
                                        @if ($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="" class="h-full w-full object-cover" loading="lazy" />
                                        @endif
                                    </div>
                                    <span class="flex-1 text-sm font-medium">{{ $product->title }}</span>
                                    <span class="text-base opacity-60 group-hover:opacity-100">→</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a
                        href="{{ $href }}"
                        target="{{ $target }}"
                        @if ($rel) rel="{{ $rel }}" @endif
                        class="{{ $clh['link_class'] }} shadow-md hover:shadow-xl {{ $link->show_icon ? 'pl-4 pr-12' : '' }}"
                        style="{{ $clh['link_style'] }}"
                    >
                        @if ($link->show_icon)
                            <span
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
                                style="background-color: {{ $brandColor }}22; color: {{ $brandColor }};"
                            >
                                <x-brand-icon :name="$link->iconName()" class="h-5 w-5" />
                            </span>
                        @endif
                        <span class="flex-1 text-center">{{ $link->title }}</span>
                        <span class="absolute right-4 text-lg opacity-0 transition group-hover:opacity-100" style="color: var(--clh-accent);" aria-hidden="true">→</span>
                    </a>
                @endif
            @endforeach
        </div>

        @if ($showPlatformBranding)
            <p class="mt-14 text-center text-xs opacity-55" style="color: var(--clh-text-muted);">
                {{ $branding['bio']['platform_credit'] }}
                <a href="{{ route('home') }}" class="underline decoration-dotted underline-offset-4 hover:opacity-100" style="color: var(--clh-accent);">{{ $branding['bio']['platform_url_label'] }}</a>
            </p>
        @endif
    </div>
@endsection
