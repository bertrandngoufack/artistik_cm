<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column is-12">
            <!-- Breadcrumb -->
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li><a href="<?= base_url('admin/examens') ?>">Examens</a></li>
                    <li><a href="<?= base_url('admin/examens/grades') ?>">Gestion des Notes</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Saisie des Notes</a></li>
                </ul>
            </nav>

            <!-- Header -->
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <h1 class="title">Saisie des Notes</h1>
                        <p class="subtitle"><?= esc($exam['name']) ?></p>
                    </div>
                </div>
                <div class="level-right">
                    <div class="level-item">
                        <a href="<?= base_url('admin/examens/grades') ?>" class="button is-info">
                            <span class="icon">
                                <i class="fas fa-arrow-left"></i>
                            </span>
                            <span>Retour</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Informations de l'examen -->
            <div class="box">
                <h3 class="title is-5">
                    <span class="icon">
                        <i class="fas fa-info-circle"></i>
                    </span>
                    Informations de l'Examen
                </h3>
                
                <div class="columns">
                    <div class="column is-3">
                        <p><strong>Type :</strong> <span class="tag is-info"><?= esc($exam['exam_type']) ?></span></p>
                    </div>
                    <div class="column is-3">
                        <p><strong>Date :</strong> <?= date('d/m/Y', strtotime($exam['exam_date'])) ?></p>
                    </div>
                    <div class="column is-3">
                        <p><strong>Note Max :</strong> <?= esc($exam['total_marks']) ?>/20</p>
                    </div>
                    <div class="column is-3">
                        <p><strong>Statut :</strong> 
                            <span class="tag <?= $exam['status'] == 'COMPLETED' ? 'is-success' : 'is-warning' ?>">
                                <?= esc($exam['status']) ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Formulaire de saisie des notes -->
            <div class="box">
                <form action="<?= base_url('admin/examens/grades/store') ?>" method="post" id="gradesForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="exam_id" value="<?= $exam['id'] ?>">
                    
                    <div class="table-container">
                        <table class="table is-fullwidth is-striped">
                            <thead>
                                <tr>
                                    <th>Matricule</th>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Note (/<?= esc($exam['total_marks']) ?>)</th>
                                    <th>Pourcentage</th>
                                    <th>Statut</th>
                                    <th>Commentaires</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($students)): ?>
                                    <?php foreach ($students as $student): ?>
                                        <?php 
                                        $existingGrade = null;
                                        foreach ($grades as $grade) {
                                            if ($grade['student_id'] == $student['id']) {
                                                $existingGrade = $grade;
                                                break;
                                            }
                                        }
                                        ?>
                                        <tr>
                                            <td><?= esc($student['matricule']) ?></td>
                                            <td><?= esc($student['last_name']) ?></td>
                                            <td><?= esc($student['first_name']) ?></td>
                                            <td>
                                                <div class="field">
                                                    <div class="control">
                                                        <input class="input grade-input <?= session('errors.grades.' . $student['id'] . '.marks') ? 'is-danger' : '' ?>" 
                                                               type="number" 
                                                               name="grades[<?= $student['id'] ?>][marks]" 
                                                               value="<?= old('grades.' . $student['id'] . '.marks', $existingGrade['marks_obtained'] ?? '') ?>" 
                                                               min="0" 
                                                               max="<?= esc($exam['total_marks']) ?>" 
                                                               step="0.25"
                                                               data-student="<?= $student['id'] ?>"
                                                               data-max="<?= esc($exam['total_marks']) ?>"
                                                               required>
                                                    </div>
                                                    <?php if (session('errors.grades.' . $student['id'] . '.marks')): ?>
                                                        <p class="help is-danger"><?= session('errors.grades.' . $student['id'] . '.marks') ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="percentage-display" id="percentage-<?= $student['id'] ?>">
                                                    <?php if ($existingGrade): ?>
                                                        <?= number_format(($existingGrade['marks_obtained'] / $exam['total_marks']) * 100, 1) ?>%
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-display" id="status-<?= $student['id'] ?>">
                                                    <?php if ($existingGrade): ?>
                                                        <?php if ($existingGrade['marks_obtained'] >= 10): ?>
                                                            <span class="tag is-success">Réussi</span>
                                                        <?php else: ?>
                                                            <span class="tag is-danger">Échoué</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="field">
                                                    <div class="control">
                                                        <textarea class="textarea" 
                                                                  name="grades[<?= $student['id'] ?>][comments]" 
                                                                  rows="2" 
                                                                  placeholder="Commentaires optionnels"><?= old('grades.' . $student['id'] . '.comments', $existingGrade['remarks'] ?? '') ?></textarea>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="has-text-centered">
                                            <p class="has-text-grey">Aucun élève trouvé pour cette classe</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="field is-grouped mt-4">
                        <div class="control">
                            <button type="submit" class="button is-primary">
                                <span class="icon">
                                    <i class="fas fa-save"></i>
                                </span>
                                <span>Enregistrer les Notes</span>
                            </button>
                        </div>
                        <div class="control">
                            <a href="<?= base_url('admin/examens/grades') ?>" class="button is-light">
                                <span class="icon">
                                    <i class="fas fa-times"></i>
                                </span>
                                <span>Annuler</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Statistiques de l'examen -->
            <div class="box">
                <h3 class="title is-5">
                    <span class="icon">
                        <i class="fas fa-chart-bar"></i>
                    </span>
                    Statistiques de l'Examen
                </h3>
                
                <div class="columns">
                    <div class="column is-3">
                        <div class="box has-text-centered">
                            <p class="heading">Total Élèves</p>
                            <p class="title" id="total-students"><?= count($students) ?></p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="box has-text-centered">
                            <p class="heading">Notes Saisies</p>
                            <p class="title" id="grades-entered">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="box has-text-centered">
                            <p class="heading">Moyenne</p>
                            <p class="title" id="average-score">0.00</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="box has-text-centered">
                            <p class="heading">Taux de Réussite</p>
                            <p class="title" id="pass-rate">0%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const gradeInputs = document.querySelectorAll('.grade-input');
    const maxMarks = <?= esc($exam['total_marks']) ?>;
    
    gradeInputs.forEach(input => {
        input.addEventListener('input', function() {
            const studentId = this.dataset.student;
            const value = parseFloat(this.value) || 0;
            const max = parseFloat(this.dataset.max);
            
            // Validation stricte (0-20)
            if (value < 0) {
                this.value = 0;
            } else if (value > max) {
                this.value = max;
            }
            
            // Calcul du pourcentage
            const percentage = max > 0 ? (value / max) * 100 : 0;
            const percentageDisplay = document.getElementById(`percentage-${studentId}`);
            percentageDisplay.textContent = percentage.toFixed(1) + '%';
            
            // Affichage du statut
            const statusDisplay = document.getElementById(`status-${studentId}`);
            if (value >= 10) {
                statusDisplay.innerHTML = '<span class="tag is-success">Réussi</span>';
            } else {
                statusDisplay.innerHTML = '<span class="tag is-danger">Échoué</span>';
            }
            
            updateStatistics();
        });
    });
    
    function updateStatistics() {
        const grades = Array.from(gradeInputs).map(input => parseFloat(input.value) || 0);
        const validGrades = grades.filter(grade => grade > 0);
        
        const totalStudents = grades.length;
        const gradesEntered = validGrades.length;
        const averageScore = validGrades.length > 0 ? validGrades.reduce((a, b) => a + b, 0) / validGrades.length : 0;
        const passedCount = validGrades.filter(grade => grade >= 10).length;
        const passRate = validGrades.length > 0 ? (passedCount / validGrades.length) * 100 : 0;
        
        document.getElementById('total-students').textContent = totalStudents;
        document.getElementById('grades-entered').textContent = gradesEntered;
        document.getElementById('average-score').textContent = averageScore.toFixed(2);
        document.getElementById('pass-rate').textContent = passRate.toFixed(1) + '%';
    }
    
    // Validation du formulaire
    document.getElementById('gradesForm').addEventListener('submit', function(e) {
        const gradeInputs = document.querySelectorAll('.grade-input');
        let isValid = true;
        
        gradeInputs.forEach(input => {
            const value = parseFloat(input.value);
            const max = parseFloat(input.dataset.max);
            
            if (isNaN(value) || value < 0 || value > max) {
                input.classList.add('is-danger');
                isValid = false;
            } else {
                input.classList.remove('is-danger');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Veuillez corriger les notes invalides. Les notes doivent être entre 0 et ' + maxMarks);
        }
    });
    
    // Initialiser les statistiques
    updateStatistics();
});
</script>

<?= $this->endSection() ?>
