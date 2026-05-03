<?php

return [
    'title' => 'Deployment & updates',
    'intro' => 'Release channel: public versions.json (pCloud). Optional: Git deployment — fetch origin and run the server update script (composer, npm build, migrations, caches).',

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

    'git_section' => 'Git deployment (optional)',
    'git_check_failed' => 'Git remote check failed.',
    'git_update_available' => 'New commits on origin for this branch.',
    'git_up_to_date' => 'Git branch is up to date with origin.',

    'no_git' => 'No Git repository at the configured path — Git actions are hidden. You can still use the release ZIP from the manifest.',
    'script_missing' => 'The script scripts/update-from-git.sh was not found.',
    'path_label' => 'Repository path',

    'branch' => 'Branch',
    'local' => 'This server (HEAD)',
    'remote' => 'Origin (after fetch)',
    'unknown' => 'Not checked yet',
    'check' => 'Check (manifest + Git)',
    'checking' => 'Checking…',

    'dirty_title' => 'Uncommitted local changes',
    'dirty_hint' => 'The standard Git update will stop. Commit or stash, or use the advanced action (risk of overwriting).',

    'apply' => 'Run Git update now',
    'applying' => 'Running update…',
    'apply_force' => 'Git update (ignore dirty tree)',
    'apply_confirm' => 'Start the full Git update script (may take several minutes)? The site can briefly be unstable.',

    'success_title' => 'Git update finished',
    'failure_title' => 'Git update failed',

    'output_hint' => 'Last lines of the log (full output is in laravel.log).',
];
