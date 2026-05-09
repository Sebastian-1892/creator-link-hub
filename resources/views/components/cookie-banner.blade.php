@php
    $bio = $branding['bio'] ?? [];
@endphp
<div id="clh-cookie-banner" class="hidden fixed bottom-0 inset-x-0 z-50 p-4">
    <div class="max-w-3xl mx-auto rounded-xl border p-4 shadow-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-sm bg-white" style="border-color: var(--brand-border, #e8e2d5); color: var(--brand-text, #1a1a1a);">
        <p>{{ $bio['cookie_text'] ?? '' }}</p>
        <button type="button" id="clh-cookie-ok" class="shrink-0 rounded-full px-5 py-2 font-semibold transition hover:opacity-95" style="background: var(--brand-primary, #dc4b3f); color: var(--brand-primary-contrast, #ffffff);">{{ $bio['cookie_button'] ?? __('Verstanden') }}</button>
    </div>
</div>
<script>
    (function () {
        var b = document.getElementById('clh-cookie-banner');
        if (!b || localStorage.getItem('clh_cookie_ok')) return;
        b.classList.remove('hidden');
        document.getElementById('clh-cookie-ok')?.addEventListener('click', function () {
            localStorage.setItem('clh_cookie_ok', '1');
            b.remove();
        });
    })();
</script>
