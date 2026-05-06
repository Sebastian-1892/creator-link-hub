<?php

return [
    'title' => 'Deployment & updates',
    'intro' => 'Release channel: public versions.json (pCloud). Server update: composer, npm build, migrations and caches — after replacing app files (e.g. new ZIP), without Git.',

    'manifest_section' => 'Release channel (versions.json)',
    'manifest_url_label' => 'Manifest URL',
    'manifest_installed' => 'Installed (this app)',
    'manifest_latest' => 'Latest (manifest)',
    'manifest_min_php' => 'Minimum PHP (manifest)',
    'manifest_open_changelog' => 'Changelog',
    'manifest_open_download' => 'Download ZIP',
    'manifest_update_available' => 'A newer release is published in the manifest.',
    'manifest_up_to_date' => 'This installation matches the latest manifest version.',
    'manifest_check_failed' => 'Could not load the release manifest.',
    'manifest_not_loaded' => 'Manifest not loaded yet — use Check.',

    'shell_section' => 'Server update (no Git)',
    'shell_intro' => 'For a new version: deploy the ZIP from the manifest (replace files), then run “Run dependencies & migrations” here or via SSH. Git is not used.',

    'script_missing' => 'The script scripts/update-application.sh was not found.',
    'script_ready' => 'Update script found',
    'path_label' => 'Install path (CLH_APP_ROOT)',

    'check' => 'Check (release manifest)',
    'checking' => 'Checking…',

    'apply' => 'Run dependencies & migrations',
    'applying' => 'Running update…',
    'apply_confirm' => 'Start the update script (composer, npm, migrations, caches; may take several minutes)? The site may briefly be unstable.',

    'success_title' => 'Application update finished',
    'failure_title' => 'Application update failed',

    'output_hint' => 'Last lines of the log (full output is in laravel.log).',
];
