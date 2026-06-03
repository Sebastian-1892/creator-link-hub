<?php

use App\Services\HtmlSanitizerService;

test('html sanitizer removes script tags', function () {
    $sanitizer = app(HtmlSanitizerService::class);

    $result = $sanitizer->sanitize('<section><p>Hello</p><script>alert(1)</script></section>');

    expect($result)->toContain('Hello')
        ->and($result)->not->toContain('script')
        ->and($result)->not->toContain('alert');
});

test('html sanitizer strips inline event handlers', function () {
    $sanitizer = app(HtmlSanitizerService::class);

    $result = $sanitizer->sanitize('<a href="https://example.com" onclick="alert(1)">Link</a>');

    expect($result)->toContain('href="https://example.com"')
        ->and($result)->not->toContain('onclick');
});
