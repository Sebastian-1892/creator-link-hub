<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Services\AvatarImageProcessor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileAvatarController extends Controller
{
    public function store(Request $request, AvatarImageProcessor $avatarProcessor): RedirectResponse
    {
        $profile = $this->profileForCurrentUser();
        $this->authorize('update', $profile);

        $maxKb = (int) config('creator.avatar.max_upload_kb', 8192);

        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:'.$maxKb],
        ], $this->validationMessages());

        if (! $this->publicStorageIsWritable()) {
            return back()->withErrors([
                'avatar' => __('Der Speicher für Profilbilder ist auf dem Server nicht beschreibbar. Bitte den Support kontaktieren.'),
            ]);
        }

        try {
            $path = $avatarProcessor->storeFromUpload($request->file('avatar'));
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors([
                'avatar' => __('Das Profilbild konnte nicht verarbeitet werden. Bitte ein anderes Bild (JPG oder PNG) versuchen.'),
            ]);
        }

        if ($profile->avatar_path) {
            Storage::disk('public')->delete($profile->avatar_path);
        }

        $profile->update(['avatar_path' => $path]);

        return redirect()
            ->route('bio.edit')
            ->with('avatar_notice', __('Profilbild gespeichert — es wurde automatisch verkleinert.'));
    }

    protected function profileForCurrentUser(): Profile
    {
        $workspace = auth()->user()?->currentWorkspace();
        abort_if(! $workspace || ! $workspace->profile, 404);

        return $workspace->profile;
    }

    protected function publicStorageIsWritable(): bool
    {
        try {
            $probe = 'avatars/.clh-write-test';
            Storage::disk('public')->put($probe, '1');
            Storage::disk('public')->delete($probe);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array<string, string>
     */
    protected function validationMessages(): array
    {
        return [
            'avatar.required' => __('Bitte eine Bilddatei auswählen.'),
            'avatar.image' => __('Bitte eine Bilddatei wählen (JPG, PNG, GIF oder WebP). iPhone-HEIC bitte zuerst als JPG speichern.'),
            'avatar.mimes' => __('Erlaubte Formate: JPG, PNG, GIF oder WebP.'),
            'avatar.max' => __('Die Datei ist zu groß (maximal 8 MB).'),
            'avatar.uploaded' => __('Die Datei konnte nicht empfangen werden — oft liegt die Upload-Größe am Server (PHP). Bitte ein kleineres Bild versuchen oder den Support kontaktieren.'),
        ];
    }
}
