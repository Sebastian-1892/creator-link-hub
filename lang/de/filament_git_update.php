<?php

return [
    'title' => 'Anwendungs-Update',
    'intro' => 'Die Schaltfläche unten lädt **keine** neue Programmversion — sie aktualisiert nur Abhängigkeiten, Frontend-Builds und Datenbank-Migrationen für die Dateien, die **bereits** im Tenant-Verzeichnis liegen. Eine neue Version wird zentral vom Cloud-Host ausgerollt (`clh-rollout-all-tenants.sh`).',

    'shell_section' => 'Abhängigkeiten & Migrationen',
    'shell_intro' => 'Composer, npm/Vite-Build, Migrationen und Caches für diese Tenant-Installation neu ausführen.',

    'script_missing' => 'Das Skript scripts/update-application.sh wurde nicht gefunden.',
    'script_ready' => 'Update-Skript gefunden',
    'path_label' => 'Installationspfad (Tenant)',

    'apply' => 'Abhängigkeiten & Migrationen ausführen',
    'applying' => 'Update läuft…',
    'apply_confirm' => 'Das Update-Skript starten (Composer, npm, Migrationen, Caches; kann mehrere Minuten dauern)? Die Seite kann kurz instabil sein.',

    'success_title' => 'Anwendungs-Update abgeschlossen',
    'failure_title' => 'Anwendungs-Update fehlgeschlagen',

    'output_truncated_middle' => '[… Mitte der Konsolen-Ausgabe wurde gekürzt …]',

    'output_hint' => 'Vollständiges Log: storage/logs/laravel.log. Dieser Schritt holt **keine** neue Programmversion — nur Composer/npm/Migrationen für den aktuellen Ordner. Für eine neuere Version den Cloud-Host ausrollen lassen.',
];
