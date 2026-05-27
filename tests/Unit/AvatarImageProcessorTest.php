<?php

use App\Services\AvatarImageProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

test('stores a large png as a smaller optimized file', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('GD extension required');
    }

    $source = imagecreatetruecolor(2400, 2400);
    $color = imagecolorallocate($source, 120, 80, 200);
    imagefill($source, 0, 0, $color);
    $tmp = tempnam(sys_get_temp_dir(), 'avatar-test-');
    imagepng($source, $tmp);
    imagedestroy($source);

    $file = new UploadedFile($tmp, 'large.png', 'image/png', null, true);

    $processor = new AvatarImageProcessor;
    $path = $processor->storeFromUpload($file);

    expect($path)->toStartWith('avatars/')
        ->and(Storage::disk('public')->exists($path))->toBeTrue()
        ->and(Storage::disk('public')->size($path))->toBeLessThan(200_000);

    @unlink($tmp);
})->skip(fn () => ! extension_loaded('gd'), 'GD extension required');
