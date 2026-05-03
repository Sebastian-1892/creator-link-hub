<?php

return [
    'title' => 'Déploiement et mises à jour',
    'intro' => 'Canal de publication : versions.json public (pCloud). Optionnel : déploiement Git — comparer origin et exécuter le script de mise à jour serveur (Composer, build npm, migrations, caches).',

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

    'git_section' => 'Déploiement Git (optionnel)',
    'git_check_failed' => 'Échec de la vérification Git distante.',
    'git_update_available' => 'Nouveaux commits sur origin pour cette branche.',
    'git_up_to_date' => 'La branche Git est à jour avec origin.',

    'no_git' => 'Pas de dépôt Git au chemin configuré — actions Git masquées. Vous pouvez utiliser le ZIP du manifeste.',
    'script_missing' => 'Script scripts/update-from-git.sh introuvable.',
    'path_label' => 'Chemin du dépôt',

    'branch' => 'Branche',
    'local' => 'Ce serveur (HEAD)',
    'remote' => 'Origin (après fetch)',
    'unknown' => 'Pas encore vérifié',
    'check' => 'Vérifier (manifeste + Git)',
    'checking' => 'Vérification…',

    'dirty_title' => 'Modifications locales non commitées',
    'dirty_hint' => 'La mise à jour Git standard s’arrête. Committez ou stash — ou action avancée (risque d’écrasement).',

    'apply' => 'Lancer la mise à jour Git',
    'applying' => 'Mise à jour…',
    'apply_force' => 'Mise à jour Git malgré les changements locaux',
    'apply_confirm' => 'Lancer le script de mise à jour Git complet (plusieurs minutes) ? Instabilité possible.',

    'success_title' => 'Mise à jour Git terminée',
    'failure_title' => 'Échec de la mise à jour Git',

    'output_hint' => 'Extrait de la sortie (complet dans laravel.log).',
];
