<div id="clh-cookie-banner" class="hidden fixed bottom-0 inset-x-0 z-50 p-4">
    <div class="max-w-3xl mx-auto rounded-xl border p-4 shadow-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-sm" style="background: var(--brand-card, #1e293b); border-color: var(--brand-border, rgba(255,255,255,0.14)); color: var(--brand-text, #f8fafc);">
        <p>{{ __('Wir verwenden notwendige Cookies für Login, Sicherheit und Analytics. Details in der Datenschutzerklärung.') }}</p>
        <button type="button" id="clh-cookie-ok" class="shrink-0 rounded-full px-5 py-2 font-semibold transition hover:opacity-95" style="background: var(--brand-primary, #6366f1); color: var(--brand-primary-contrast, #ffffff);">{{ __('Verstanden') }}</button>
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
