<?php

/**
 * Script de mise à jour du module Scolarité pour utiliser les classes du module Études
 */

echo "=== Mise à jour du module Scolarité pour utiliser les classes du module Études ===\n\n";

// Charger CodeIgniter
require_once 'public/index.php';

use App\Models\ClassModel;
use App\Models\CycleModel;

try {
    // Initialiser les modèles
    $classModel = new ClassModel();
    $cycleModel = new CycleModel();
    
    echo "✓ Modèles initialisés\n";
    
    // Vérifier si les cycles existent
    $cycles = $cycleModel->getActiveCycles();
    if (empty($cycles)) {
        echo "❌ Aucun cycle trouvé. Veuillez d'abord exécuter le script de création des tables études.\n";
        exit(1);
    }
    
    echo "✓ Cycles trouvés: " . count($cycles) . "\n";
    
    // Vérifier si les classes existent
    $classes = $classModel->getActiveClasses();
    if (empty($classes)) {
        echo "❌ Aucune classe trouvée. Veuillez d'abord exécuter le script de création des tables études.\n";
        exit(1);
    }
    
    echo "✓ Classes trouvées: " . count($classes) . "\n";
    
    // Mettre à jour la vue des élèves pour inclure les cycles
    $studentsViewPath = 'app/Views/admin/scolarite/students.php';
    if (file_exists($studentsViewPath)) {
        echo "Mise à jour de la vue des élèves...\n";
        
        // Lire le contenu actuel
        $content = file_get_contents($studentsViewPath);
        
        // Ajouter le filtre par cycle si il n'existe pas déjà
        if (!strpos($content, 'cycle_id')) {
            // Trouver la section des filtres et ajouter le filtre cycle
            $filterPattern = '/<div class="field">\s*<label class="label">Classe<\/label>/';
            $cycleFilter = '<div class="field">
                <label class="label">Cycle</label>
                <div class="control">
                    <div class="select is-fullwidth">
                        <select name="cycle_id" id="cycle_filter">
                            <option value="">Tous les cycles</option>
                            <?php foreach ($cycles as $cycle): ?>
                                <option value="<?= $cycle[\'id\'] ?>" <?= ($filters[\'cycle_id\'] == $cycle[\'id\']) ? \'selected\' : \'\' ?>>
                                    <?= $cycle[\'name\'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="field">';
            
            $content = preg_replace($filterPattern, $cycleFilter, $content);
            
            // Ajouter le JavaScript pour filtrer les classes par cycle
            $jsPattern = '/<\/script>/';
            $cycleJs = '
            // Filtre des classes par cycle
            document.getElementById(\'cycle_filter\').addEventListener(\'change\', function() {
                const cycleId = this.value;
                const classSelect = document.getElementById(\'class_filter\');
                
                // Réinitialiser les options de classe
                classSelect.innerHTML = \'<option value="">Toutes les classes</option>\';
                
                if (cycleId) {
                    // Filtrer les classes par cycle
                    const classes = <?= json_encode($classes) ?>;
                    classes.forEach(function(classe) {
                        if (classe.cycle_id == cycleId) {
                            const option = document.createElement(\'option\');
                            option.value = classe.id;
                            option.textContent = classe.name;
                            classSelect.appendChild(option);
                        }
                    });
                } else {
                    // Afficher toutes les classes
                    const classes = <?= json_encode($classes) ?>;
                    classes.forEach(function(classe) {
                        const option = document.createElement(\'option\');
                        option.value = classe.id;
                        option.textContent = classe.name;
                        classSelect.appendChild(option);
                    });
                }
            });
            </script>';
            
            $content = preg_replace($jsPattern, $cycleJs, $content);
            
            // Sauvegarder le fichier
            file_put_contents($studentsViewPath, $content);
            echo "✓ Vue des élèves mise à jour\n";
        } else {
            echo "✓ Vue des élèves déjà mise à jour\n";
        }
    }
    
    // Mettre à jour le contrôleur Scolarité pour inclure les cycles
    $scolariteControllerPath = 'app/Controllers/Scolarite.php';
    if (file_exists($scolariteControllerPath)) {
        echo "Mise à jour du contrôleur Scolarité...\n";
        
        // Lire le contenu actuel
        $content = file_get_contents($scolariteControllerPath);
        
        // Ajouter l'import du CycleModel si il n'existe pas
        if (!strpos($content, 'use App\\Models\\CycleModel;')) {
            $importPattern = '/use App\\Models\\DisciplineModel;/';
            $newImport = 'use App\Models\DisciplineModel;
use App\Models\CycleModel;';
            $content = preg_replace($importPattern, $newImport, $content);
        }
        
        // Ajouter la propriété cycleModel si elle n'existe pas
        if (!strpos($content, 'protected $cycleModel;')) {
            $propertyPattern = '/protected \$disciplineModel;/';
            $newProperty = 'protected $disciplineModel;
    protected $cycleModel;';
            $content = preg_replace($propertyPattern, $newProperty, $content);
        }
        
        // Ajouter l'initialisation du cycleModel dans le constructeur
        if (!strpos($content, '$this->cycleModel = new CycleModel();')) {
            $constructorPattern = '/\$this->disciplineModel = new DisciplineModel\(\);/';
            $newConstructor = '$this->disciplineModel = new DisciplineModel();
        $this->cycleModel = new CycleModel();';
            $content = preg_replace($constructorPattern, $newConstructor, $content);
        }
        
        // Mettre à jour la méthode students pour inclure les cycles
        $studentsMethodPattern = '/public function students\(\)\s*\{[^}]*\}/s';
        $newStudentsMethod = 'public function students()
    {
        $academicYear = $this->request->getGet(\'academic_year\') ?: $this->getCurrentAcademicYear();
        $classId = $this->request->getGet(\'class_id\');
        $cycleId = $this->request->getGet(\'cycle_id\');
        $status = $this->request->getGet(\'status\');
        $search = $this->request->getGet(\'search\');
        
        // Récupération des élèves avec filtres
        $students = $this->getStudentsWithFilters($academicYear, $classId, $status, $search);
        
        // Statistiques des élèves
        $studentStats = $this->getStudentStats($academicYear, $classId, $status);
        
        // Classes disponibles (filtrées par cycle si spécifié)
        $classes = $this->getActiveClasses($academicYear, $cycleId);
        
        // Cycles disponibles
        $cycles = $this->cycleModel->getActiveCycles();
        
        $data = $this->prepareViewData([
            \'title\' => \'Gestion des Élèves\',
            \'students\' => $students,
            \'studentStats\' => $studentStats,
            \'classes\' => $classes,
            \'cycles\' => $cycles,
            \'filters\' => [
                \'academic_year\' => $academicYear,
                \'class_id\' => $classId,
                \'cycle_id\' => $cycleId,
                \'status\' => $status,
                \'search\' => $search
            ]
        ]);

        return view(\'admin/scolarite/students\', $data);
    }';
        
        $content = preg_replace($studentsMethodPattern, $newStudentsMethod, $content);
        
        // Ajouter la méthode getActiveClasses mise à jour
        $getActiveClassesPattern = '/protected function getActiveClasses\([^)]*\)[^}]*\}/s';
        $newGetActiveClassesMethod = 'protected function getActiveClasses($academicYear = null, $cycleId = null)
    {
        $builder = $this->pdo->prepare("
            SELECT DISTINCT c.*, cy.name as cycle_name 
            FROM classes c 
            LEFT JOIN cycles cy ON c.cycle_id = cy.id 
            WHERE c.is_active = 1
        ");
        
        $params = [];
        
        if ($academicYear) {
            $builder = $this->pdo->prepare("
                SELECT DISTINCT c.*, cy.name as cycle_name 
                FROM classes c 
                LEFT JOIN cycles cy ON c.cycle_id = cy.id 
                WHERE c.is_active = 1 AND c.academic_year = ?
            ");
            $params[] = $academicYear;
        }
        
        if ($cycleId) {
            $builder = $this->pdo->prepare("
                SELECT DISTINCT c.*, cy.name as cycle_name 
                FROM classes c 
                LEFT JOIN cycles cy ON c.cycle_id = cy.id 
                WHERE c.is_active = 1 AND c.cycle_id = ?
            ");
            $params = [$cycleId];
        }
        
        if ($academicYear && $cycleId) {
            $builder = $this->pdo->prepare("
                SELECT DISTINCT c.*, cy.name as cycle_name 
                FROM classes c 
                LEFT JOIN cycles cy ON c.cycle_id = cy.id 
                WHERE c.is_active = 1 AND c.academic_year = ? AND c.cycle_id = ?
            ");
            $params = [$academicYear, $cycleId];
        }
        
        $builder->execute($params);
        return $builder->fetchAll(PDO::FETCH_ASSOC);
    }';
        
        $content = preg_replace($getActiveClassesPattern, $newGetActiveClassesMethod, $content);
        
        // Sauvegarder le fichier
        file_put_contents($scolariteControllerPath, $content);
        echo "✓ Contrôleur Scolarité mis à jour\n";
    }
    
    echo "\n=== Mise à jour terminée avec succès ===\n";
    echo "Le module Scolarité utilise maintenant les classes du module Études.\n";
    echo "Vous pouvez accéder à: http://localhost:8080/admin/scolarite/students\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
