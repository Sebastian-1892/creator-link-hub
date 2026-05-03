<?php

return [
    'title' => 'Deployment & Updates',
    'intro' => 'Release-Kanal: öffentliche versions.json (pCloud). Optional: Git-Installation — origin abgleichen und Server-Update-Skript ausführen (Composer, npm-Build, Migrationen, Caches).',

    'manifest_section' => 'Release-Kanal (versions.json)',
    'manifest_url_label' => 'Manifest-URL',
    'manifest_installed' => 'Installiert (diese App)',
    'manifest_latest' => 'Neueste Version (Manifest)',
    'manifest_min_php' => 'Mindest-PHP (Manifest)',
    'manifest_open_changelog' => 'Changelog',
    'manifest_open_download' => 'ZIP herunterladen',
    'manifest_update_available' => 'Im Manifest liegt eine neuere Release-Version vor.',
    'manifest_up_to_date' => 'Diese Installation entspricht der neuesten Version im Manifest.',
    'manifest_check_failed' => 'Das Release-Manifest konnte nicht geladen werden.',
    'manifest_not_loaded' => 'Manifest noch nicht geladen — bitte „Prüfen“.',

    'git_section' => 'Git-Deployment (optional)',
    'git_check_failed' => 'Git-Remote-Prüfung fehlgeschlagen.',
    'git_update_available' => 'Neue Commits auf origin für diesen Branch.',
    'git_up_to_date' => 'Git-Branch ist mit origin auf dem neuesten Stand.',

    'no_git' => 'Kein Git-Repository am konfigurierten Pfad — Git-Aktionen entfallen. Das Release-ZIP aus dem Manifest kannst du trotzdem nutzen.',
    'script_missing' => 'Das Skript scripts/update-from-git.sh wurde nicht gefunden.',
    'path_label' => 'Repository-Pfad',

    'branch' => 'Branch',
    'local' => 'Dieser Server (HEAD)',
    'remote' => 'Origin (nach fetch)',
    'unknown' => 'Noch nicht geprüft',
    'check' => 'Prüfen (Manifest + Git)',
    'checking' => 'Prüfe…',

    'dirty_title' => 'Lokale, nicht committete Änderungen',
    'dirty_hint' => 'Das Standard-Git-Update bricht ab. Bitte committen/stashen — oder die erweiterte Aktion (Überschreiben möglich).',

    'apply' => 'Git-Update jetzt ausführen',
    'applying' => 'Update läuft…',
    'apply_force' => 'Git-Update trotz lokaler Änderungen',
    'apply_confirm' => 'Das vollständige Git-Update-Skript starten (kann mehrere Minuten dauern)? Die Seite kann kurz instabil sein.',

    'success_title' => 'Git-Update abgeschlossen',
    'failure_title' => 'Git-Update fehlgeschlagen',

    'output_hint' => 'Auszug aus der Ausgabe (vollständig in laravel.log).',
];
