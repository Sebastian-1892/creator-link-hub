<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function makeDesignTestImage(): array
{
    $source = imagecreatetruecolor(800, 600);
    $color = imagecolorallocate($source, 80, 140, 220);
    imagefill($source, 0, 0, $color);
    $tmp = tempnam(sys_get_temp_dir(), 'design-img-');
    imagepng($source, $tmp);
    imagedestroy($source);

    return [
        'path' => $tmp,
        'file' => new UploadedFile($tmp, 'design.png', 'image/png', null, true),
    ];
}

test('wallpaper image can be uploaded and deleted', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('GD extension required');
    }

    Storage::fake('public');

    $user = User::factory()->create();
    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    ['path' => $tmp, 'file' => $file] = makeDesignTestImage();

    $this->actingAs($user)
        ->post(route('design.wallpaper.store'), ['wallpaper' => $file])
        ->assertRedirect(route('design.edit', ['section' => 'wallpaper']))
        ->assertSessionHas('design_notice');

    $profile->refresh();
    expect($profile->wallpaper_image_path)->not->toBeNull()
        ->and(Storage::disk('public')->exists($profile->wallpaper_image_path))->toBeTrue()
        ->and($profile->theme_variables['wallpaper']['style'] ?? null)->toBe('image');

    $this->actingAs($user)
        ->delete(route('design.wallpaper.destroy'))
        ->assertRedirect(route('design.edit', ['section' => 'wallpaper']));

    $profile->refresh();
    expect($profile->wallpaper_image_path)->toBeNull();

    @unlink($tmp);
});

test('banner image can be uploaded and deleted', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('GD extension required');
    }

    Storage::fake('public');

    $user = User::factory()->create();
    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    ['path' => $tmp, 'file' => $file] = makeDesignTestImage();

    $this->actingAs($user)
        ->post(route('design.banner.store'), ['banner' => $file])
        ->assertRedirect(route('design.edit', ['section' => 'header']))
        ->assertSessionHas('design_notice');

    $profile->refresh();
    expect($profile->banner_image_path)->not->toBeNull()
        ->and($profile->header_layout)->toBe('banner')
        ->and(Storage::disk('public')->exists($profile->banner_image_path))->toBeTrue();

    $this->actingAs($user)
        ->delete(route('design.banner.destroy'))
        ->assertRedirect(route('design.edit', ['section' => 'header']));

    $profile->refresh();
    expect($profile->banner_image_path)->toBeNull();

    @unlink($tmp);
});
