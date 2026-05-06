<?php

return [
    'title' => 'Deployment & Updates',
    'intro' => 'Release-Kanal: öffentliche versions.json (pCloud). Server-Update: Composer, npm-Build, Migrationen und Caches — nach Austausch der App-Dateien (z. B. neues ZIP), ohne Git.',

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

    'shell_section' => 'Server-Update (ohne Git)',
    'shell_intro' => 'Neue Version: ZIP aus dem Manifest einspielen (Dateien ersetzen), dann hier oder per SSH „Abhängigkeiten & Migrationen ausführen“ starten. Es wird kein Git verwendet.',

    'script_missing' => 'Das Skript scripts/update-application.sh wurde nicht gefunden.',
    'script_ready' => 'Update-Skript gefunden',
    'path_label' => 'Installationspfad (CLH_APP_ROOT)',

    'check' => 'Prüfen (Release-Manifest)',
    'checking' => 'Prüfe…',

    'apply' => 'Abhängigkeiten & Migrationen ausführen',
    'applying' => 'Update läuft…',
    'apply_confirm' => 'Das Update-Skript starten (Composer, npm, Migrationen, Caches; kann mehrere Minuten dauern)? Die Seite kann kurz instabil sein.',

    'success_title' => 'Anwendungs-Update abgeschlossen',
    'failure_title' => 'Anwendungs-Update fehlgeschlagen',

    'output_hint' => 'Auszug aus der Ausgabe (vollständig in laravel.log).',
];
