@props([
    'previewUrl' => '',
    'previewPages' => [],
    'previewVersion' => 0,
    'previewPage' => 'home',
])

<div class="flex flex-col gap-3">
    <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
        <div class="flex min-w-0 flex-1 flex-col gap-1 sm:flex-row sm:items-center sm:gap-3">
            <label for="branding-preview-page" class="shrink-0 text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ __('admin_settings.branding.preview_page') }}
            </label>
            <select
                id="branding-preview-page"
                wire:model.live="previewPage"
                class="fi-select-input block w-full min-w-0 rounded-lg border-none bg-white py-1.5 pe-8 ps-3 text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 transition duration-75 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20 sm:max-w-xs"
            >
                @foreach ($previewPages as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <a
            href="{{ strtok($previewUrl, '?') ?: $previewUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex shrink-0 items-center gap-1 text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400"
        >
            {{ __('admin_settings.branding.preview_open') }}
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                <path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 0 0-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 0 0 .75-.75v-4a.75.75 0 0 1 1.5 0v4A2.25 2.25 0 0 1 12.75 17h-8.5A2.25 2.25 0 0 1 2 14.75v-8.5A2.25 2.25 0 0 1 4.25 4h5a.75.75 0 0 1 0 1.5h-5Z" clip-rule="evenodd" />
                <path fill-rule="evenodd" d="M6.194 12.753a.75.75 0 0 0 1.06.053L16.5 4.44v2.81a.75.75 0 0 0 1.5 0v-4.5a.75.75 0 0 0-.75-.75h-4.5a.75.75 0 0 0 0 1.5h2.553l-9.056 8.194a.75.75 0 0 0-.053 1.06Z" clip-rule="evenodd" />
            </svg>
        </a>
    </div>

    <p class="text-xs text-gray-500 dark:text-gray-400">
        {{ __('admin_settings.branding.preview_help') }}
    </p>

    <div
        class="relative overflow-hidden rounded-xl border border-gray-200 bg-gray-100 shadow-inner dark:border-gray-700 dark:bg-gray-900"
    >
        <iframe
            wire:key="branding-preview-{{ $previewPage }}-{{ $previewVersion }}"
            title="{{ __('admin_settings.branding.preview') }}"
            src="{{ $previewUrl }}"
            class="block w-full border-0 bg-white"
            style="height: min(75vh, 960px); min-height: 420px;"
            loading="lazy"
        ></iframe>
    </div>
</div>
