<?php

return [
    'title' => 'Mise à jour de l’application',
    'intro' => 'Le bouton ci-dessous ne télécharge **pas** une nouvelle version de l’application — il met à jour uniquement les dépendances, les builds front-end et les migrations pour les fichiers **déjà** présents dans ce répertoire de tenant. Les nouvelles versions sont déployées depuis le cloud-host (`clh-rollout-all-tenants.sh`).',

    'shell_section' => 'Dépendances et migrations',
    'shell_intro' => 'Relancer Composer, npm/Vite build, migrations et caches pour cette installation de tenant.',

    'script_missing' => 'Le script scripts/update-application.sh est introuvable.',
    'script_ready' => 'Script de mise à jour trouvé',
    'path_label' => 'Chemin d’installation (tenant)',

    'apply' => 'Exécuter dépendances et migrations',
    'applying' => 'Mise à jour…',
    'apply_confirm' => 'Lancer le script de mise à jour (Composer, npm, migrations, caches ; peut prendre plusieurs minutes) ? Instabilité possible.',

    'success_title' => 'Mise à jour de l’application terminée',
    'failure_title' => 'Échec de la mise à jour de l’application',

    'output_truncated_middle' => '[… partie centrale de la sortie omise …]',

    'output_hint' => 'Journal complet : storage/logs/laravel.log. Aucune version nouvelle n’est téléchargée — uniquement composer/npm/migrations pour le dossier actuel. Pour une version plus récente, déployer depuis le cloud-host.',
];
