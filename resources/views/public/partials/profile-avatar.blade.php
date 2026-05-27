@if ($profile->avatar_path)
    <img
        src="{{ \Illuminate\Support\Facades\Storage::url($profile->avatar_path) }}"
        alt=""
        class="{{ $clh['avatar_class'] }}"
        style="{{ $clh['avatar_style'] }}"
    >
@else
    <div
        class="{{ $clh['placeholder_avatar_class'] }} text-4xl"
        style="{{ $clh['placeholder_avatar_style'] }}"
    >
        {{ \Illuminate\Support\Str::substr($profile->display_name, 0, 1) }}
    </div>
@endif
