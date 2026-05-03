@php
    use Illuminate\Support\Facades\Auth;
    $locales = config('creator.filament_locales', []);
    $user = Auth::user();
    $current = ($user && is_string($user->filament_locale)) ? $user->filament_locale : app()->getLocale();
@endphp

@if (Auth::check() && ($user?->is_admin ?? false) && count($locales) > 0)
    <div
        class="fi-admin-locale-switcher me-2 flex flex-wrap items-center gap-1 border-e border-gray-200 pe-3 dark:border-white/10 md:flex-nowrap"
        role="navigation"
        aria-label="{{ __('Sprache') }}"
    >
        <span class="hidden text-xs text-gray-500 dark:text-gray-400 sm:inline">{{ __('Sprache') }}:</span>
        @foreach ($locales as $code => $meta)
            <a
                href="{{ url('/set-filament-locale/'.$code) }}"
                @class([
                    'rounded-md px-2 py-1 text-xs font-semibold transition focus:outline-none focus:ring-2 focus:ring-amber-500/50',
                    'bg-amber-500 text-white shadow-sm' => $current === $code,
                    'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10' => $current !== $code,
                ])
            >
                {{ strtoupper($code) }}
            </a>
        @endforeach
    </div>
@endif
