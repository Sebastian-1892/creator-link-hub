<?php

/**
 * Livewire-Konfiguration (angelehnt an vendor/livewire/livewire/config/livewire.php).
 * Nur Abweichungen gegenüber dem Paket-Default sind hier dokumentiert.
 */
return [

    'class_namespace' => 'App\\Livewire',

    'view_path' => resource_path('views/livewire'),

    'layout' => 'components.layouts.app',

    'lazy_placeholder' => null,

    /*
    | Temporäre Uploads: immer Disk "local" (storage/app/private/livewire-tmp),
    | damit Provisioner/ensure-laravel-storage.sh zuverlässig greift — unabhängig von FILESYSTEM_DISK.
    */
    'temporary_file_upload' => [
        'disk' => 'local',
        'rules' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:8192'],
        'directory' => 'livewire-tmp',
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 5,
        'cleanup' => true,
    ],

    'render_on_redirect' => false,

    'legacy_model_binding' => false,

    'inject_assets' => true,

    'navigate' => [
        'show_progress_bar' => true,
        'progress_bar_color' => '#2299dd',
    ],

    'inject_morph_markers' => true,

    'smart_wire_keys' => false,

    'pagination_theme' => 'tailwind',

    'release_token' => 'a',
];
