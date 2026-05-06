<?php

return [
    'title' => 'Déploiement et mises à jour',
    'intro' => 'Canal de publication : versions.json public (pCloud). Mise à jour serveur : Composer, build npm, migrations et caches — après remplacement des fichiers (ex. nouveau ZIP), sans Git.',

    'manifest_section' => 'Canal de publication (versions.json)',
    'manifest_url_label' => 'URL du manifeste',
    'manifest_installed' => 'Version installée (cette app)',
    'manifest_latest' => 'Dernière version (manifeste)',
    'manifest_min_php' => 'PHP minimum (manifeste)',
    'manifest_open_changelog' => 'Journal des modifications',
    'manifest_open_download' => 'Télécharger le ZIP',
    'manifest_update_available' => 'Une version plus récente est publiée dans le manifeste.',
    'manifest_up_to_date' => 'Cette installation correspond à la dernière version du manifeste.',
    'manifest_check_failed' => 'Impossible de charger le manifeste de publication.',
    'manifest_not_loaded' => 'Manifeste non chargé — utilisez Vérifier.',

    'shell_section' => 'Mise à jour serveur (sans Git)',
    'shell_intro' => 'Pour une nouvelle version : déployer le ZIP du manifeste (remplacer les fichiers), puis lancer « Exécuter dépendances et migrations » ici ou en SSH. Git n’est pas utilisé.',

    'script_missing' => 'Le script scripts/update-application.sh est introuvable.',
    'script_ready' => 'Script de mise à jour trouvé',
    'path_label' => 'Chemin d’installation (CLH_APP_ROOT)',

    'check' => 'Vérifier (manifeste de publication)',
    'checking' => 'Vérification…',

    'apply' => 'Exécuter dépendances et migrations',
    'applying' => 'Mise à jour…',
    'apply_confirm' => 'Lancer le script de mise à jour (Composer, npm, migrations, caches ; peut prendre plusieurs minutes) ? Instabilité possible.',

    'success_title' => 'Mise à jour de l’application terminée',
    'failure_title' => 'Échec de la mise à jour de l’application',

    'output_hint' => 'Extrait de la sortie (complet dans laravel.log).',
];
