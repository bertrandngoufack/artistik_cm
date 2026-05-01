<?php
/**
 * Script de test pour vérifier la conformité du CRUD enseignant
 * avec les autres modules du système LYCOL
 */

// Configuration de base
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST DE CONFORMITÉ DU MODULE ENSEIGNANTS ===\n\n";

// 1. Vérification de la structure du contrôleur
echo "1. Vérification de la structure du contrôleur Enseignants...\n";

$controllerFile = 'app/Controllers/Enseignants.php';
if (file_exists($controllerFile)) {
    $controllerContent = file_get_contents($controllerFile);
    
    // Vérification des méthodes CRUD
    $crudMethods = [
        'index' => 'Liste des enseignants',
        'create' => 'Création d\'un enseignant',
        'store' => 'Enregistrement d\'un enseignant',
        'show' => 'Affichage d\'un enseignant',
        'edit' => 'Modification d\'un enseignant',
        'update' => 'Mise à jour d\'un enseignant',
        'delete' => 'Suppression d\'un enseignant'
    ];
    
    foreach ($crudMethods as $method => $description) {
        if (strpos($controllerContent, "public function $method") !== false) {
            echo "   ✓ Méthode $method() trouvée - $description\n";
        } else {
            echo "   ✗ Méthode $method() MANQUANTE - $description\n";
        }
    }
    
    // Vérification de la validation
    if (strpos($controllerContent, '$this->validate') !== false) {
        echo "   ✓ Validation des données implémentée\n";
    } else {
        echo "   ✗ Validation des données MANQUANTE\n";
    }
    
    // Vérification de la gestion d'erreurs
    if (strpos($controllerContent, 'redirect()->back()->withInput()->with') !== false) {
        echo "   ✓ Gestion d'erreurs avec retour des données implémentée\n";
    } else {
        echo "   ✗ Gestion d'erreurs avec retour des données MANQUANTE\n";
    }
    
} else {
    echo "   ✗ Fichier contrôleur non trouvé\n";
}

echo "\n";

// 2. Vérification du modèle
echo "2. Vérification du modèle TeacherModel...\n";

$modelFile = 'app/Models/TeacherModel.php';
if (file_exists($modelFile)) {
    $modelContent = file_get_contents($modelFile);
    
    // Vérification des propriétés du modèle
    $modelProperties = [
        'protected $table' => 'Nom de la table',
        'protected $primaryKey' => 'Clé primaire',
        'protected $allowedFields' => 'Champs autorisés',
        'protected $validationRules' => 'Règles de validation',
        'protected $useTimestamps' => 'Horodatage automatique'
    ];
    
    foreach ($modelProperties as $property => $description) {
        if (strpos($modelContent, $property) !== false) {
            echo "   ✓ $description configurée\n";
        } else {
            echo "   ✗ $description MANQUANTE\n";
        }
    }
    
    // Vérification des méthodes personnalisées
    $customMethods = [
        'getTeacherWithUser' => 'Récupération avec utilisateur',
        'getActiveTeachers' => 'Récupération des enseignants actifs',
        'getTeacherSubjects' => 'Récupération des matières',
        'getTeacherClasses' => 'Récupération des classes'
    ];
    
    foreach ($customMethods as $method => $description) {
        if (strpos($modelContent, "public function $method") !== false) {
            echo "   ✓ Méthode $method() trouvée - $description\n";
        } else {
            echo "   ✗ Méthode $method() MANQUANTE - $description\n";
        }
    }
    
} else {
    echo "   ✗ Fichier modèle non trouvé\n";
}

echo "\n";

// 3. Vérification des vues
echo "3. Vérification des vues...\n";

$viewFiles = [
    'app/Views/admin/enseignants/index.php' => 'Page d\'accueil',
    'app/Views/admin/enseignants/list.php' => 'Liste des enseignants',
    'app/Views/admin/enseignants/create.php' => 'Création',
    'app/Views/admin/enseignants/edit.php' => 'Modification',
    'app/Views/admin/enseignants/show.php' => 'Affichage'
];

foreach ($viewFiles as $file => $description) {
    if (file_exists($file)) {
        echo "   ✓ Vue $description trouvée\n";
    } else {
        echo "   ✗ Vue $description MANQUANTE\n";
    }
}

echo "\n";

// 4. Vérification des routes
echo "4. Vérification des routes...\n";

$routesFile = 'app/Config/Routes.php';
if (file_exists($routesFile)) {
    $routesContent = file_get_contents($routesFile);
    
    $requiredRoutes = [
        'Enseignants::index' => 'Page d\'accueil',
        'Enseignants::create' => 'Création',
        'Enseignants::store' => 'Enregistrement',
        'Enseignants::show' => 'Affichage',
        'Enseignants::edit' => 'Modification',
        'Enseignants::update' => 'Mise à jour',
        'Enseignants::delete' => 'Suppression'
    ];
    
    foreach ($requiredRoutes as $route => $description) {
        if (strpos($routesContent, $route) !== false) {
            echo "   ✓ Route $description configurée\n";
        } else {
            echo "   ✗ Route $description MANQUANTE\n";
        }
    }
    
} else {
    echo "   ✗ Fichier routes non trouvé\n";
}

echo "\n";

// 5. Comparaison avec d'autres modules
echo "5. Comparaison avec d'autres modules...\n";

$otherControllers = [
    'app/Controllers/Scolarite.php' => 'Scolarité',
    'app/Controllers/Etudes.php' => 'Études',
    'app/Controllers/Economat.php' => 'Économat'
];

foreach ($otherControllers as $file => $moduleName) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Vérification de la structure commune
        $commonPatterns = [
            'public function __construct' => 'Constructeur',
            '$this->validate' => 'Validation',
            'redirect()->back()->withInput()' => 'Gestion d\'erreurs',
            'with(\'success\'' => 'Messages de succès',
            'with(\'error\'' => 'Messages d\'erreur'
        ];
        
        echo "   Module $moduleName:\n";
        foreach ($commonPatterns as $pattern => $description) {
            if (strpos($content, $pattern) !== false) {
                echo "     ✓ $description présente\n";
            } else {
                echo "     ✗ $description MANQUANTE\n";
            }
        }
    }
}

echo "\n";

// 6. Vérification de la base de données
echo "6. Vérification de la base de données...\n";

$dbFiles = [
    'database/lycol_schema.sql' => 'Schéma principal',
    'database/lycol_schema_fixed.sql' => 'Schéma corrigé',
    'database/add_teachers_table.sql' => 'Table teachers'
];

foreach ($dbFiles as $file => $description) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, 'teachers') !== false || strpos($content, 'CREATE TABLE') !== false) {
            echo "   ✓ $description trouvé\n";
        } else {
            echo "   ✗ $description ne contient pas la table teachers\n";
        }
    } else {
        echo "   ✗ $description non trouvé\n";
    }
}

echo "\n";

// 7. Recommandations
echo "7. Recommandations pour améliorer la conformité:\n";

$recommendations = [
    "S'assurer que la table 'teachers' existe dans la base de données",
    "Vérifier que toutes les méthodes CRUD sont implémentées",
    "Ajouter la validation côté client (JavaScript)",
    "Implémenter la pagination pour la liste des enseignants",
    "Ajouter des filtres de recherche avancés",
    "Implémenter l'export des données (CSV, PDF)",
    "Ajouter des logs d'audit pour les modifications",
    "Implémenter la gestion des permissions",
    "Ajouter des tests unitaires",
    "Documenter les méthodes du contrôleur"
];

foreach ($recommendations as $index => $recommendation) {
    echo "   " . ($index + 1) . ". $recommendation\n";
}

echo "\n=== FIN DU TEST DE CONFORMITÉ ===\n";
?>

