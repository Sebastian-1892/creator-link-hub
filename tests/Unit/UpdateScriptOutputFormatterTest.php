<?php

use App\Support\UpdateScriptOutputFormatter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('short output is unchanged', function () {
    $s = str_repeat('a', 100);
    expect(UpdateScriptOutputFormatter::forNotification($s, true))->toBe($s);
});

test('long output includes head and tail when failed', function () {
    $head = str_repeat('H', 6000);
    $tail = str_repeat('T', 6000);
    $middle = str_repeat('M', 10000);
    $combined = $head.$middle.$tail;

    $out = UpdateScriptOutputFormatter::forNotification($combined, true);

    expect($out)
        ->toContain(str_repeat('H', 100))
        ->toContain(str_repeat('T', 100))
        ->not->toContain('MMMM')
        ->and(strlen($out))->toBeLessThan(strlen($combined));
});
