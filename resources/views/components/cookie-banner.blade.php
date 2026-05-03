<div id="clh-cookie-banner" class="hidden fixed bottom-0 inset-x-0 z-50 p-4">
    <div class="max-w-3xl mx-auto rounded-lg bg-slate-900 border border-slate-700 p-4 shadow-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-sm text-slate-200">
        <p>{{ __('Wir verwenden notwendige Cookies für Login, Sicherheit und Analytics. Details in der Datenschutzerklärung.') }}</p>
        <button type="button" id="clh-cookie-ok" class="shrink-0 rounded-md bg-cyan-500 px-4 py-2 font-medium text-slate-950 hover:bg-cyan-400">{{ __('Verstanden') }}</button>
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
