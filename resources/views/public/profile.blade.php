@extends('layouts.public', ['profile' => $profile, 'showPlatformBranding' => $showPlatformBranding])

@section('content')
    @php
        $clh = clh_public_theme($profile);
    @endphp
    <div class="max-w-md mx-auto px-4 py-12 pb-28">
        <header class="mb-8">
            @include('public.partials.profile-header', ['profile' => $profile, 'clh' => $clh])
        </header>

        @php
            $topLevelLinks = $profile->links
                ->whereNull('parent_link_id')
                ->reject(fn ($link) => $link->link_type === 'product')
                ->values();
            $productsByCollection = $profile->links
                ->where('link_type', 'product')
                ->whereNotNull('parent_link_id')
                ->groupBy('parent_link_id');
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
                    <details name="clh-shop" class="clh-shop-accordion group">
                        <summary
                            class="{{ $clh['link_class'] }} w-full cursor-pointer list-none [&::-webkit-details-marker]:hidden"
                            style="{{ $clh['link_style'] }}"
                        >
                            <span
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
                                style="background-color: rgba(99, 102, 241, 0.14); color: var(--clh-accent);"
                            >
                                <x-brand-icon name="shop" class="h-5 w-5" />
                            </span>
                            <span class="flex-1 text-left">
                                <span class="block text-sm font-semibold">{{ $link->title }}</span>
                                <span class="block text-xs opacity-75" style="color: var(--clh-text-muted);">{{ __('Shop') }}</span>
                            </span>
                            <span class="rounded-full px-2 py-1 text-xs font-semibold" style="background: rgba(99, 102, 241, 0.14); color: var(--clh-accent);">
                                {{ $products->count() }}
                            </span>
                            <span class="clh-shop-chevron ml-2 text-lg transition-transform duration-200" style="color: var(--clh-accent);" aria-hidden="true">›</span>
                        </summary>

                        <div class="mt-2 space-y-3">
                            @if ($products->isEmpty())
                                <div class="rounded-2xl px-4 py-3 text-sm" style="background: rgba(255,255,255,.5); color: var(--clh-text-muted);">
                                    {{ __('Diese Collection ist noch leer.') }}
                                </div>
                            @else
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
                                            class="group rounded-2xl border border-white/50 bg-white/75 p-2 shadow-sm transition hover:shadow-md"
                                        >
                                            <div class="h-28 rounded-xl overflow-hidden bg-white/40">
                                                @if ($product->image_url)
                                                    <img src="{{ $product->image_url }}" alt="" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy" />
                                                @endif
                                            </div>
                                            <div class="mt-2 flex items-center gap-2">
                                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full" style="background: rgba(99, 102, 241, 0.14); color: var(--clh-accent);">
                                                    <x-brand-icon name="product" class="h-4 w-4" />
                                                </span>
                                                <span class="flex-1 truncate text-sm font-semibold">{{ $product->title }}</span>
                                                <span class="text-base opacity-65 group-hover:opacity-100">→</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </details>
                @else
                    @if ($link->preset_key === 'spotify')
                        @include('public.partials.spotify-link', ['link' => $link, 'clh' => $clh])
                    @else
                    <a
                        href="{{ $href }}"
                        target="{{ $target }}"
                        @if ($rel) rel="{{ $rel }}" @endif
                        class="{{ $clh['link_class'] }}{{ $link->show_icon ? ' pl-4 pr-12' : '' }}"
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
                        <span class="clh-link-arrow" aria-hidden="true">→</span>
                    </a>
                    @endif
                @endif
            @endforeach
        </div>

        @if ($showPlatformBranding)
            <p class="mt-14 text-center text-xs opacity-55" style="color: var(--clh-text-muted);">
                {{ $branding['bio']['platform_credit'] }}
                <a href="{{ route('home') }}" class="underline decoration-dotted underline-offset-4 hover:opacity-100" style="color: var(--clh-accent);">{{ $branding['brand_name'] }}</a>
            </p>
        @endif
    </div>
@endsection
