<?php

return [
    'title' => 'Aggiornamento applicazione',
    'intro' => 'Il pulsante qui sotto **non** scarica una nuova versione dell’applicazione — aggiorna solo dipendenze, build front-end e migrazioni per i file **già** presenti in questa directory di tenant. Le nuove versioni vengono distribuite dal cloud-host (`clh-rollout-all-tenants.sh`).',

    'shell_section' => 'Dipendenze e migrazioni',
    'shell_intro' => 'Eseguire di nuovo Composer, npm/Vite build, migrazioni e cache per questa installazione di tenant.',

    'script_missing' => 'Script scripts/update-application.sh non trovato.',
    'script_ready' => 'Script di aggiornamento trovato',
    'path_label' => 'Percorso installazione (tenant)',

    'apply' => 'Esegui dipendenze e migrazioni',
    'applying' => 'Aggiornamento…',
    'apply_confirm' => 'Avviare lo script di aggiornamento (Composer, npm, migrazioni, cache; può richiedere diversi minuti)? Possibile instabilità.',

    'success_title' => 'Aggiornamento applicazione completato',
    'failure_title' => 'Aggiornamento applicazione non riuscito',

    'output_truncated_middle' => '[… parte centrale dell’output omessa …]',

    'output_hint' => 'Log completo: storage/logs/laravel.log. Nessuna nuova versione viene scaricata — solo composer/npm/migrazioni per la cartella attuale. Per una versione più recente, distribuire dal cloud-host.',
];
