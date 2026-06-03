@once
    @php
        $clhProfileCssPath = resource_path('css/clh-profile.css');
        $clhProfileCss = is_readable($clhProfileCssPath)
            ? (string) file_get_contents($clhProfileCssPath)
            : '';
    @endphp
    @if ($clhProfileCss !== '')
        <style>{!! $clhProfileCss !!}</style>
    @endif
@endonce
