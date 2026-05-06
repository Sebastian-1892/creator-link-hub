<?php

return [
    'title' => 'Deploy e aggiornamenti',
    'intro' => 'Canale release: versions.json pubblico (pCloud). Aggiornamento server: Composer, build npm, migrazioni e cache — dopo la sostituzione dei file (es. nuovo ZIP), senza Git.',

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

    'shell_section' => 'Aggiornamento server (senza Git)',
    'shell_intro' => 'Per una nuova versione: distribuire lo ZIP dal manifest (sostituire i file), poi avviare «Esegui dipendenze e migrazioni» qui o via SSH. Git non viene usato.',

    'script_missing' => 'Script scripts/update-application.sh non trovato.',
    'script_ready' => 'Script di aggiornamento trovato',
    'path_label' => 'Percorso installazione (CLH_APP_ROOT)',

    'check' => 'Controlla (manifest release)',
    'checking' => 'Verifica…',

    'apply' => 'Esegui dipendenze e migrazioni',
    'applying' => 'Aggiornamento…',
    'apply_confirm' => 'Avviare lo script di aggiornamento (Composer, npm, migrazioni, cache; può richiedere diversi minuti)? Possibile instabilità.',

    'success_title' => 'Aggiornamento applicazione completato',
    'failure_title' => 'Aggiornamento applicazione non riuscito',

    'output_hint' => 'Estratto dell’output (completo in laravel.log).',
];
