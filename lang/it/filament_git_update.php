<?php

return [
    'title' => 'Deploy e aggiornamenti',
    'intro' => 'Canale release: versions.json pubblico (pCloud). Opzionale: deploy Git — confronta origin ed esegui lo script di aggiornamento sul server (Composer, build npm, migrazioni, cache).',

    'manifest_section' => 'Canale release (versions.json)',
    'manifest_url_label' => 'URL manifest',
    'manifest_installed' => 'Installata (questa app)',
    'manifest_latest' => 'Ultima versione (manifest)',
    'manifest_min_php' => 'PHP minimo (manifest)',
    'manifest_open_changelog' => 'Changelog',
    'manifest_open_download' => 'Scarica ZIP',
    'manifest_update_available' => 'È disponibile una release più recente nel manifest.',
    'manifest_up_to_date' => 'Questa installazione corrisponde all’ultima versione nel manifest.',
    'manifest_check_failed' => 'Impossibile caricare il manifest di release.',
    'manifest_not_loaded' => 'Manifest non caricato — usa Controlla.',

    'git_section' => 'Deploy Git (opzionale)',
    'git_check_failed' => 'Verifica Git remota non riuscita.',
    'git_update_available' => 'Nuovi commit su origin per questo branch.',
    'git_up_to_date' => 'Il branch Git è aggiornato con origin.',

    'no_git' => 'Nessun repository Git nel percorso configurato — azioni Git nascoste. Puoi usare lo ZIP dal manifest.',
    'script_missing' => 'Script scripts/update-from-git.sh non trovato.',
    'path_label' => 'Percorso repository',

    'branch' => 'Branch',
    'local' => 'Questo server (HEAD)',
    'remote' => 'Origin (dopo fetch)',
    'unknown' => 'Non ancora verificato',
    'check' => 'Controlla (manifest + Git)',
    'checking' => 'Verifica…',

    'dirty_title' => 'Modifiche locali non committate',
    'dirty_hint' => 'L’aggiornamento Git standard si interrompe. Esegui commit/stash — o azione avanzata (rischio sovrascrittura).',

    'apply' => 'Esegui aggiornamento Git',
    'applying' => 'Aggiornamento…',
    'apply_force' => 'Aggiorna Git ignorando modifiche locali',
    'apply_confirm' => 'Avviare lo script Git completo (minuti)? Possibile instabilità.',

    'success_title' => 'Aggiornamento Git completato',
    'failure_title' => 'Aggiornamento Git non riuscito',

    'output_hint' => 'Estratto dell’output (completo in laravel.log).',
];
