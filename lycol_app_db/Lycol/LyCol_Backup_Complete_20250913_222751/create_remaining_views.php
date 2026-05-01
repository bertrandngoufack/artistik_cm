<?php

// Script pour créer toutes les vues manquantes
echo "=== CRÉATION DES VUES MANQUANTES ===\n\n";

$views = [
    'bibliotheque/index.php' => [
        'title' => 'Gestion Bibliothèque',
        'subtitle' => 'Livres, prêts et retours',
        'cards' => [
            ['icon' => 'fas fa-book', 'title' => 'Livres', 'subtitle' => 'Catalogue des livres', 'url' => 'admin/bibliotheque/books', 'color' => 'primary'],
            ['icon' => 'fas fa-plus-circle', 'title' => 'Nouveau Livre', 'subtitle' => 'Ajouter un livre', 'url' => 'admin/bibliotheque/books/create', 'color' => 'info'],
            ['icon' => 'fas fa-hand-holding', 'title' => 'Prêts', 'subtitle' => 'Gestion des prêts', 'url' => 'admin/bibliotheque/loans', 'color' => 'success'],
            ['icon' => 'fas fa-users', 'title' => 'Lecteurs', 'subtitle' => 'Gestion des lecteurs', 'url' => 'admin/bibliotheque/readers', 'color' => 'warning']
        ],
        'stats' => [
            ['label' => 'Livres disponibles', 'value' => '0', 'color' => 'primary'],
            ['label' => 'Prêts actifs', 'value' => '0', 'color' => 'info'],
            ['label' => 'Lecteurs inscrits', 'value' => '0', 'color' => 'success'],
            ['label' => 'Retours en retard', 'value' => '0', 'color' => 'danger']
        ]
    ],
    'messagerie/index.php' => [
        'title' => 'Gestion Messagerie',
        'subtitle' => 'Communication interne',
        'cards' => [
            ['icon' => 'fas fa-envelope', 'title' => 'Messages', 'subtitle' => 'Boîte de réception', 'url' => 'admin/messagerie/messages', 'color' => 'primary'],
            ['icon' => 'fas fa-plus-circle', 'title' => 'Nouveau Message', 'subtitle' => 'Envoyer un message', 'url' => 'admin/messagerie/messages/create', 'color' => 'info'],
            ['icon' => 'fas fa-paper-plane', 'title' => 'Messages Envoyés', 'subtitle' => 'Messages expédiés', 'url' => 'admin/messagerie/sent', 'color' => 'success'],
            ['icon' => 'fas fa-archive', 'title' => 'Archives', 'subtitle' => 'Messages archivés', 'url' => 'admin/messagerie/archives', 'color' => 'warning']
        ],
        'stats' => [
            ['label' => 'Messages non lus', 'value' => '0', 'color' => 'primary'],
            ['label' => 'Messages envoyés', 'value' => '0', 'color' => 'info'],
            ['label' => 'Utilisateurs actifs', 'value' => '0', 'color' => 'success'],
            ['label' => 'Messages archivés', 'value' => '0', 'color' => 'warning']
        ]
    ],
    'enseignants/index.php' => [
        'title' => 'Gestion Enseignants',
        'subtitle' => 'Personnel enseignant',
        'cards' => [
            ['icon' => 'fas fa-chalkboard-teacher', 'title' => 'Enseignants', 'subtitle' => 'Liste des enseignants', 'url' => 'admin/enseignants/teachers', 'color' => 'primary'],
            ['icon' => 'fas fa-plus-circle', 'title' => 'Nouvel Enseignant', 'subtitle' => 'Ajouter un enseignant', 'url' => 'admin/enseignants/teachers/create', 'color' => 'info'],
            ['icon' => 'fas fa-calendar-alt', 'title' => 'Planning', 'subtitle' => 'Emploi du temps', 'url' => 'admin/enseignants/schedule', 'color' => 'success'],
            ['icon' => 'fas fa-chart-bar', 'title' => 'Évaluations', 'subtitle' => 'Notes et évaluations', 'url' => 'admin/enseignants/evaluations', 'color' => 'warning']
        ],
        'stats' => [
            ['label' => 'Enseignants actifs', 'value' => '0', 'color' => 'primary'],
            ['label' => 'Matières enseignées', 'value' => '0', 'color' => 'info'],
            ['label' => 'Heures de cours', 'value' => '0h', 'color' => 'success'],
            ['label' => 'Évaluations en cours', 'value' => '0', 'color' => 'warning']
        ]
    ],
    'securite/index.php' => [
        'title' => 'Gestion Sécurité',
        'subtitle' => 'Sécurité et accès',
        'cards' => [
            ['icon' => 'fas fa-shield-alt', 'title' => 'Utilisateurs', 'subtitle' => 'Gestion des utilisateurs', 'url' => 'admin/securite/users', 'color' => 'primary'],
            ['icon' => 'fas fa-key', 'title' => 'Permissions', 'subtitle' => 'Gestion des rôles', 'url' => 'admin/securite/permissions', 'color' => 'info'],
            ['icon' => 'fas fa-history', 'title' => 'Logs', 'subtitle' => 'Journal des activités', 'url' => 'admin/securite/logs', 'color' => 'success'],
            ['icon' => 'fas fa-lock', 'title' => 'Sécurité', 'subtitle' => 'Paramètres de sécurité', 'url' => 'admin/securite/settings', 'color' => 'warning']
        ],
        'stats' => [
            ['label' => 'Utilisateurs actifs', 'value' => '0', 'color' => 'primary'],
            ['label' => 'Connexions aujourd\'hui', 'value' => '0', 'color' => 'info'],
            ['label' => 'Tentatives échouées', 'value' => '0', 'color' => 'danger'],
            ['label' => 'Dernière connexion', 'value' => 'N/A', 'color' => 'warning']
        ]
    ],
    'statistiques/index.php' => [
        'title' => 'Statistiques',
        'subtitle' => 'Tableaux de bord et rapports',
        'cards' => [
            ['icon' => 'fas fa-chart-pie', 'title' => 'Tableaux de Bord', 'subtitle' => 'Vue d\'ensemble', 'url' => 'admin/statistiques/dashboard', 'color' => 'primary'],
            ['icon' => 'fas fa-chart-line', 'title' => 'Rapports', 'subtitle' => 'Rapports détaillés', 'url' => 'admin/statistiques/reports', 'color' => 'info'],
            ['icon' => 'fas fa-download', 'title' => 'Exports', 'subtitle' => 'Exporter les données', 'url' => 'admin/statistiques/exports', 'color' => 'success'],
            ['icon' => 'fas fa-cog', 'title' => 'Configuration', 'subtitle' => 'Paramètres des rapports', 'url' => 'admin/statistiques/settings', 'color' => 'warning']
        ],
        'stats' => [
            ['label' => 'Étudiants inscrits', 'value' => '0', 'color' => 'primary'],
            ['label' => 'Enseignants actifs', 'value' => '0', 'color' => 'info'],
            ['label' => 'Classes ouvertes', 'value' => '0', 'color' => 'success'],
            ['label' => 'Taux de réussite', 'value' => '0%', 'color' => 'warning']
        ]
    ],
    'configuration/index.php' => [
        'title' => 'Configuration',
        'subtitle' => 'Paramètres du système',
        'cards' => [
            ['icon' => 'fas fa-cog', 'title' => 'Général', 'subtitle' => 'Paramètres généraux', 'url' => 'admin/configuration/general', 'color' => 'primary'],
            ['icon' => 'fas fa-database', 'title' => 'Base de Données', 'subtitle' => 'Configuration BDD', 'url' => 'admin/configuration/database', 'color' => 'info'],
            ['icon' => 'fas fa-envelope', 'title' => 'Email', 'subtitle' => 'Configuration email', 'url' => 'admin/configuration/email', 'color' => 'success'],
            ['icon' => 'fas fa-backup', 'title' => 'Sauvegarde', 'subtitle' => 'Sauvegarde et restauration', 'url' => 'admin/configuration/backup', 'color' => 'warning']
        ],
        'stats' => [
            ['label' => 'Version système', 'value' => '1.0.0', 'color' => 'primary'],
            ['label' => 'Dernière sauvegarde', 'value' => 'N/A', 'color' => 'info'],
            ['label' => 'Espace disque', 'value' => 'N/A', 'color' => 'success'],
            ['label' => 'Statut système', 'value' => 'OK', 'color' => 'success']
        ]
    ]
];

foreach ($views as $viewPath => $config) {
    $fullPath = 'app/Views/' . $viewPath;
    $dir = dirname($fullPath);
    
    // Créer le répertoire s'il n'existe pas
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✅ Répertoire créé: $dir\n";
    }
    
    // Générer le contenu de la vue
    $content = generateViewContent($config);
    
    // Écrire le fichier
    file_put_contents($fullPath, $content);
    echo "✅ Vue créée: $fullPath\n";
}

echo "\n=== CRÉATION TERMINÉE ===\n";

function generateViewContent($config) {
    $cards = '';
    foreach ($config['cards'] as $card) {
        $cards .= "        <div class=\"column is-3\">
            <div class=\"card\">
                <div class=\"card-content\">
                    <div class=\"media\">
                        <div class=\"media-left\">
                            <figure class=\"image is-48x48\">
                                <i class=\"{$card['icon']} fa-2x has-text-{$card['color']}\"></i>
                            </figure>
                        </div>
                        <div class=\"media-content\">
                            <p class=\"title is-5\">{$card['title']}</p>
                            <p class=\"subtitle is-6\">{$card['subtitle']}</p>
                        </div>
                    </div>
                    <div class=\"content\">
                        <a href=\"<?= base_url('{$card['url']}') ?>\" class=\"button is-{$card['color']} is-fullwidth\">
                            {$card['title']}
                        </a>
                    </div>
                </div>
            </div>
        </div>\n";
    }
    
    $stats = '';
    foreach ($config['stats'] as $stat) {
        $stats .= "                    <div class=\"column is-3\">
                        <div class=\"has-text-centered\">
                            <p class=\"heading\">{$stat['label']}</p>
                            <p class=\"title is-3 has-text-{$stat['color']}\">{$stat['value']}</p>
                        </div>
                    </div>\n";
    }
    
    return "<?= \$this->extend('layouts/admin') ?>

<?= \$this->section('content') ?>
<div class=\"container\">
    <div class=\"columns\">
        <div class=\"column\">
            <h1 class=\"title\">{$config['title']}</h1>
            <p class=\"subtitle\">{$config['subtitle']}</p>
        </div>
    </div>
    
    <div class=\"columns is-multiline\">
$cards    </div>
    
    <!-- Statistiques rapides -->
    <div class=\"columns mt-6\">
        <div class=\"column\">
            <div class=\"box\">
                <h2 class=\"title is-4\">Statistiques</h2>
                <div class=\"columns\">
$stats                </div>
            </div>
        </div>
    </div>
</div>
<?= \$this->endSection() ?>";
}
