<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('profile avatar can be uploaded via post form', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('GD extension required');
    }

    Storage::fake('public');

    $user = User::factory()->create();
    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    $source = imagecreatetruecolor(400, 400);
    $color = imagecolorallocate($source, 40, 120, 200);
    imagefill($source, 0, 0, $color);
    $tmp = tempnam(sys_get_temp_dir(), 'avatar-feature-');
    imagepng($source, $tmp);
    imagedestroy($source);

    $file = new UploadedFile($tmp, 'avatar.png', 'image/png', null, true);

    $response = $this->actingAs($user)->post(route('bio.avatar.store'), [
        'avatar' => $file,
    ]);

    $response->assertRedirect(route('bio.edit'));
    $response->assertSessionHas('avatar_notice');

    $profile->refresh();
    expect($profile->avatar_path)->not->toBeNull()
        ->and(Storage::disk('public')->exists($profile->avatar_path))->toBeTrue();

    @unlink($tmp);
});
