<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column is-12">
            <!-- En-tête -->
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <div>
                            <h1 class="title">
                                <span class="icon">
                                    <i class="fas fa-file-alt"></i>
                                </span>
                                Bulletins Générés
                            </h1>
                            <p class="subtitle">Résultats de la génération des bulletins</p>
                        </div>
                    </div>
                </div>
                <div class="level-right">
                    <div class="level-item">
                        <div class="buttons">
                            <a href="<?= base_url('admin/examens/report-cards') ?>" class="button is-light">
                                <span class="icon">
                                    <i class="fas fa-arrow-left"></i>
                                </span>
                                <span>Retour</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations de génération -->
            <div class="box">
                <h3 class="title is-5">
                    <span class="icon">
                        <i class="fas fa-info-circle"></i>
                    </span>
                    Paramètres de Génération
                </h3>
                
                <div class="columns">
                    <div class="column is-4">
                        <div class="field">
                            <label class="label">Classe</label>
                            <div class="control">
                                <p class="has-text-weight-semibold">
                                    <?php
                                    $classModel = new \App\Models\ClassModel();
                                    $class = $classModel->find($class_id);
                                    echo $class ? esc($class['name']) : 'Classe #' . $class_id;
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="column is-4">
                        <div class="field">
                            <label class="label">Période</label>
                            <div class="control">
                                <p class="has-text-weight-semibold"><?= esc($period ?? 'Non spécifiée') ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="column is-4">
                        <div class="field">
                            <label class="label">Format</label>
                            <div class="control">
                                <span class="tag is-info"><?= esc($format ?? 'PDF') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des bulletins -->
            <?php if (!empty($students)): ?>
            <div class="box">
                <h3 class="title is-5">
                    <span class="icon">
                        <i class="fas fa-list"></i>
                    </span>
                    Bulletins Générés
                </h3>
                
                <div class="table-container">
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Élève</th>
                                <th>Moyenne Générale</th>
                                <th>Rang</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                            <?php
                            // Calculer la moyenne de l'élève
                            $studentGrades = array_filter($grades ?? [], function($grade) use ($student) {
                                return $grade['student_id'] == $student['id'];
                            });
                            
                            $totalMarks = 0;
                            $totalCoefficient = 0;
                            
                            foreach ($studentGrades as $grade) {
                                $totalMarks += $grade['marks_obtained'];
                                $totalCoefficient += 1; // Coefficient par défaut
                            }
                            
                            $average = $totalCoefficient > 0 ? round($totalMarks / $totalCoefficient, 2) : 0;
                            $status = $average >= 10 ? 'Réussi' : 'Échoué';
                            ?>
                            <tr>
                                <td>
                                    <div>
                                        <p class="has-text-weight-semibold"><?= esc($student['first_name'] . ' ' . $student['last_name']) ?></p>
                                        <p class="is-size-7 has-text-grey"><?= esc($student['matricule']) ?></p>
                                    </div>
                                </td>
                                <td>
                                    <span class="has-text-weight-semibold"><?= $average ?>/20</span>
                                </td>
                                <td>
                                    <span class="tag is-info">À calculer</span>
                                </td>
                                <td>
                                    <?php if ($average >= 10): ?>
                                        <span class="tag is-success"><?= $status ?></span>
                                    <?php else: ?>
                                        <span class="tag is-danger"><?= $status ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="buttons are-small">
                                        <a href="#" class="button is-info" onclick="generatePDF(<?= $student['id'] ?>)">
                                            <span class="icon">
                                                <i class="fas fa-download"></i>
                                            </span>
                                            <span>PDF</span>
                                        </a>
                                        <a href="#" class="button is-success" onclick="generateExcel(<?= $student['id'] ?>)">
                                            <span class="icon">
                                                <i class="fas fa-file-excel"></i>
                                            </span>
                                            <span>Excel</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Actions en lot -->
            <div class="box">
                <h3 class="title is-5">
                    <span class="icon">
                        <i class="fas fa-download"></i>
                    </span>
                    Actions en Lot
                </h3>
                
                <div class="buttons">
                    <button class="button is-info" onclick="generateAllPDF()">
                        <span class="icon">
                            <i class="fas fa-download"></i>
                        </span>
                        <span>Télécharger tous les PDF</span>
                    </button>
                    <button class="button is-success" onclick="generateAllExcel()">
                        <span class="icon">
                            <i class="fas fa-file-excel"></i>
                        </span>
                        <span>Exporter en Excel</span>
                    </button>
                    <button class="button is-warning" onclick="printAll()">
                        <span class="icon">
                            <i class="fas fa-print"></i>
                        </span>
                        <span>Imprimer tous</span>
                    </button>
                </div>
            </div>
            <?php else: ?>
            <div class="box">
                <div class="has-text-centered">
                    <p class="has-text-grey">Aucun élève trouvé pour cette classe</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function generatePDF(studentId) {
    // TODO: Implémenter la génération PDF
    alert('Génération PDF pour l\'élève ' + studentId + ' - À implémenter');
}

function generateExcel(studentId) {
    // TODO: Implémenter la génération Excel
    alert('Génération Excel pour l\'élève ' + studentId + ' - À implémenter');
}

function generateAllPDF() {
    // TODO: Implémenter la génération PDF en lot
    alert('Génération PDF en lot - À implémenter');
}

function generateAllExcel() {
    // TODO: Implémenter la génération Excel en lot
    alert('Génération Excel en lot - À implémenter');
}

function printAll() {
    // TODO: Implémenter l'impression en lot
    alert('Impression en lot - À implémenter');
}
</script>

<?= $this->endSection() ?>
