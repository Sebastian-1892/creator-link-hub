<?php

return [
    'title' => 'Application update',
    'intro' => 'The button below does **not** download a new application version — it only refreshes dependencies, frontend builds and database migrations for the files **already** present in this tenant directory. New application versions are rolled out centrally from the cloud host (`clh-rollout-all-tenants.sh`).',

    'shell_section' => 'Dependencies & migrations',
    'shell_intro' => 'Re-run Composer, npm/Vite build, migrations and caches for this tenant installation.',

    'script_missing' => 'The script scripts/update-application.sh was not found.',
    'script_ready' => 'Update script found',
    'path_label' => 'Install path (tenant)',

    'apply' => 'Run dependencies & migrations',
    'applying' => 'Running update…',
    'apply_confirm' => 'Start the update script (composer, npm, migrations, caches; may take several minutes)? The site may briefly be unstable.',

    'success_title' => 'Application update finished',
    'failure_title' => 'Application update failed',

    'output_truncated_middle' => '[… middle of console output omitted …]',

    'output_hint' => 'Full log: storage/logs/laravel.log. This step does **not** pull a new application version — only composer/npm/migrations for the current folder. For a newer version, roll out from the cloud host.',
];
