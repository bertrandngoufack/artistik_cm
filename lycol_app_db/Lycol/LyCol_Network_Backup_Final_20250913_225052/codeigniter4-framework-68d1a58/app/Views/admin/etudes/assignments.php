<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="section">
        <!-- En-tête -->
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <h1 class="title">Gestion des Assignations</h1>
                </div>
            </div>
            <div class="level-right">
                <div class="level-item">
                    <a href="<?= base_url('admin/etudes/assignments/create') ?>" class="button is-primary">
                        <span class="icon">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span>Nouvelle Assignation</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="columns is-multiline mb-5">
            <div class="column is-3">
                <div class="box has-background-primary has-text-white">
                    <div class="level">
                        <div class="level-left">
                            <div class="level-item">
                                <div>
                                    <p class="heading has-text-white">Total Assignations</p>
                                    <p class="title has-text-white"><?= $total_assignments ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="level-right">
                            <div class="level-item">
                                <span class="icon has-text-white">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="column is-3">
                <div class="box has-background-info has-text-white">
                    <div class="level">
                        <div class="level-left">
                            <div class="level-item">
                                <div>
                                    <p class="heading has-text-white">Enseignants Assignés</p>
                                    <p class="title has-text-white"><?= $teachers_assigned ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="level-right">
                            <div class="level-item">
                                <span class="icon has-text-white">
                                    <i class="fas fa-user-tie"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="column is-3">
                <div class="box has-background-success has-text-white">
                    <div class="level">
                        <div class="level-left">
                            <div class="level-item">
                                <div>
                                    <p class="heading has-text-white">Classes Couvertes</p>
                                    <p class="title has-text-white"><?= $classes_covered ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="level-right">
                            <div class="level-item">
                                <span class="icon has-text-white">
                                    <i class="fas fa-chalkboard"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="column is-3">
                <div class="box has-background-warning has-text-white">
                    <div class="level">
                        <div class="level-left">
                            <div class="level-item">
                                <div>
                                    <p class="heading has-text-white">Matières</p>
                                    <p class="title has-text-white"><?= $subjects_covered ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="level-right">
                            <div class="level-item">
                                <span class="icon has-text-white">
                                    <i class="fas fa-book"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="box">
            <div class="columns is-multiline">
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Enseignant</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select id="teacher_filter">
                                    <option value="">Tous les enseignants</option>
                                    <?php if (isset($teachers) && is_array($teachers)): ?>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <option value="<?= $teacher['id'] ?>" <?= (isset($filter_teacher_id) && $filter_teacher_id == $teacher['id']) ? 'selected' : '' ?>>
                                                <?= esc($teacher['first_name'] . ' ' . $teacher['last_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Classe</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select id="class_filter">
                                    <option value="">Toutes les classes</option>
                                    <?php if (isset($classes) && is_array($classes)): ?>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?= $class['id'] ?>" <?= (isset($filter_class_id) && $filter_class_id == $class['id']) ? 'selected' : '' ?>><?= esc($class['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Matière</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select id="subject_filter">
                                    <option value="">Toutes les matières</option>
                                    <?php if (isset($subjects) && is_array($subjects)): ?>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?= $subject['id'] ?>" <?= (isset($filter_subject_id) && $filter_subject_id == $subject['id']) ? 'selected' : '' ?>><?= esc($subject['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Cycle</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select id="cycle_filter">
                                    <option value="">Tous les cycles</option>
                                    <?php if (isset($cycles) && is_array($cycles)): ?>
                                        <?php foreach ($cycles as $cycle): ?>
                                            <option value="<?= $cycle['id'] ?>" <?= (isset($filter_cycle_id) && $filter_cycle_id == $cycle['id']) ? 'selected' : '' ?>><?= esc($cycle['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Année Académique</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select id="academic_year_filter">
                                    <option value="">Toutes les années</option>
                                    <?php if (isset($available_academic_years) && is_array($available_academic_years)): ?>
                                        <?php foreach ($available_academic_years as $year): ?>
                                            <option value="<?= $year ?>" <?= (isset($filter_academic_year) && $filter_academic_year == $year) ? 'selected' : '' ?>><?= $year ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message de filtrage actif -->
        <?php if (isset($filter_teacher_id) || isset($filter_class_id) || isset($filter_subject_id) || isset($filter_cycle_id) || isset($filter_academic_year)): ?>
            <div class="notification is-info is-light mt-3 mb-3">
                <div class="content">
                    <p class="has-text-weight-bold">
                        <span class="icon"><i class="fas fa-filter"></i></span>
                        Filtrage actif
                    </p>
                    <p class="is-size-7">
                        <?php
                        $activeFilters = [];
                        if (isset($filter_teacher_id)) {
                            $teacher = array_filter($teachers, fn($t) => $t['id'] == $filter_teacher_id);
                            if (!empty($teacher)) {
                                $teacher = reset($teacher);
                                $activeFilters[] = "Enseignant: " . $teacher['first_name'] . ' ' . $teacher['last_name'];
                            }
                        }
                        if (isset($filter_class_id)) {
                            $class = array_filter($classes, fn($c) => $c['id'] == $filter_class_id);
                            if (!empty($class)) {
                                $class = reset($class);
                                $activeFilters[] = "Classe: " . $class['name'];
                            }
                        }
                        if (isset($filter_subject_id)) {
                            $subject = array_filter($subjects, fn($s) => $s['id'] == $filter_subject_id);
                            if (!empty($subject)) {
                                $subject = reset($subject);
                                $activeFilters[] = "Matière: " . $subject['name'];
                            }
                        }
                        if (isset($filter_cycle_id)) {
                            $cycle = array_filter($cycles, fn($c) => $c['id'] == $filter_cycle_id);
                            if (!empty($cycle)) {
                                $cycle = reset($cycle);
                                $activeFilters[] = "Cycle: " . $cycle['name'];
                            }
                        }
                        if (isset($filter_academic_year)) {
                            $activeFilters[] = "Année Académique: " . $filter_academic_year;
                        }
                        echo implode(' | ', $activeFilters);
                        ?>
                    </p>
                    <a href="<?= base_url('admin/etudes/assignments') ?>" class="button is-small is-info">
                        <span class="icon"><i class="fas fa-times"></i></span>
                        <span>Effacer les filtres</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tableau des assignations -->
        <div class="box">
            <div class="table-container">
                <table class="table is-fullwidth is-striped is-hoverable">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0)" style="cursor: pointer;">
                                Enseignant
                                <span class="icon is-small">
                                    <i class="fas fa-sort"></i>
                                </span>
                            </th>
                            <th onclick="sortTable(1)" style="cursor: pointer;">
                                Classe
                                <span class="icon is-small">
                                    <i class="fas fa-sort"></i>
                                </span>
                            </th>
                            <th onclick="sortTable(2)" style="cursor: pointer;">
                                Cycle
                                <span class="icon is-small">
                                    <i class="fas fa-sort"></i>
                                </span>
                            </th>
                            <th onclick="sortTable(3)" style="cursor: pointer;">
                                Matière
                                <span class="icon is-small">
                                    <i class="fas fa-sort"></i>
                                </span>
                            </th>
                            <th onclick="sortTable(4)" style="cursor: pointer;">
                                Année Académique
                                <span class="icon is-small">
                                    <i class="fas fa-sort"></i>
                                </span>
                            </th>
                            <th onclick="sortTable(5)" style="cursor: pointer;">
                                Statut
                                <span class="icon is-small">
                                    <i class="fas fa-sort"></i>
                                </span>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($assignments) && is_array($assignments)): ?>
                            <?php foreach ($assignments as $assignment): ?>
                                <tr>
                                    <td>
                                        <div class="media">
                                            <div class="media-left">
                                                <span class="icon has-text-info">
                                                    <i class="fas fa-user-tie"></i>
                                                </span>
                                            </div>
                                            <div class="media-content">
                                                <p class="has-text-weight-semibold">
                                                    <?= esc(($assignment['first_name'] ?? '') . ' ' . ($assignment['last_name'] ?? '')) ?>
                                                </p>
                                                <p class="is-size-7 has-text-grey">
                                                    <?= esc($assignment['teacher_email'] ?? '') ?>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="tag is-info">
                                            <?= esc($assignment['class_name'] ?? 'N/A') ?>
                                        </span>
                                        <br>
                                        <small class="has-text-grey">
                                            Niveau: <?= esc($assignment['class_level'] ?? 'N/A') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="tag is-warning">
                                            <?= esc($assignment['cycle_name'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= esc($assignment['subject_name'] ?? 'N/A') ?></strong>
                                        <br>
                                        <small class="has-text-grey">
                                            <?= esc($assignment['subject_code'] ?? '') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="tag is-light">
                                            <?= esc($assignment['academic_year'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($assignment['is_active']): ?>
                                            <span class="tag is-success">Actif</span>
                                        <?php else: ?>
                                            <span class="tag is-danger">Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="buttons are-small">
                                            <a href="<?= base_url('admin/etudes/assignments/view/' . $assignment['id']) ?>" 
                                               class="button is-info" title="Voir">
                                                <span class="icon">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                            </a>
                                            <a href="<?= base_url('admin/etudes/assignments/edit/' . $assignment['id']) ?>" 
                                               class="button is-warning" title="Modifier">
                                                <span class="icon">
                                                    <i class="fas fa-edit"></i>
                                                </span>
                                            </a>
                                            <button class="button is-danger" 
                                                    onclick="deleteAssignment(<?= $assignment['id'] ?>)" 
                                                    title="Supprimer">
                                                <span class="icon">
                                                    <i class="fas fa-trash"></i>
                                                </span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="has-text-centered">
                                    <p class="has-text-grey">Aucune assignation trouvée</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Vue d'ensemble par enseignant -->
        <?php if (isset($assignments) && !empty($assignments)): ?>
        <div class="box">
            <h3 class="title is-4">Vue d'ensemble par enseignant</h3>
            <div class="columns is-multiline">
                <?php
                $teacherAssignments = [];
                foreach ($assignments as $assignment) {
                    $teacherId = $assignment['teacher_id'];
                    if (!isset($teacherAssignments[$teacherId])) {
                        $teacherAssignments[$teacherId] = [
                            'name' => ($assignment['first_name'] ?? '') . ' ' . ($assignment['last_name'] ?? ''),
                            'assignments' => []
                        ];
                    }
                    $teacherAssignments[$teacherId]['assignments'][] = $assignment;
                }
                ?>
                
                <?php foreach ($teacherAssignments as $teacherId => $teacherData): ?>
                <div class="column is-6">
                    <div class="card">
                        <header class="card-header">
                            <p class="card-header-title">
                                <?= esc($teacherData['name']) ?>
                            </p>
                        </header>
                        <div class="card-content">
                            <div class="content">
                                <?php foreach ($teacherData['assignments'] as $assignment): ?>
                                    <div class="mb-2">
                                        <span class="tag is-info mr-2">
                                            <?= esc($assignment['class_name']) ?>
                                        </span>
                                        <span class="tag is-warning mr-2">
                                            <?= esc($assignment['subject_name']) ?>
                                        </span>
                                        <span class="tag is-light">
                                            <?= esc($assignment['cycle_name']) ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Filtres
document.getElementById('teacher_filter').addEventListener('change', filterAssignments);
document.getElementById('class_filter').addEventListener('change', filterAssignments);
document.getElementById('subject_filter').addEventListener('change', filterAssignments);
document.getElementById('cycle_filter').addEventListener('change', filterAssignments);
document.getElementById('academic_year_filter').addEventListener('change', filterAssignments);

function filterAssignments() {
    const teacherFilter = document.getElementById('teacher_filter').value;
    const classFilter = document.getElementById('class_filter').value;
    const subjectFilter = document.getElementById('subject_filter').value;
    const cycleFilter = document.getElementById('cycle_filter').value;
    const academicYearFilter = document.getElementById('academic_year_filter').value;
    
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const teacherCell = row.cells[0].textContent;
        const classCell = row.cells[1].textContent;
        const cycleCell = row.cells[2].textContent;
        const subjectCell = row.cells[3].textContent;
        const academicYearCell = row.cells[4].textContent;
        
        const matchesTeacher = !teacherFilter || teacherCell.includes(teacherFilter);
        const matchesClass = !classFilter || classCell.includes(classFilter);
        const matchesSubject = !subjectFilter || subjectCell.includes(subjectFilter);
        const matchesCycle = !cycleFilter || cycleCell.includes(cycleFilter);
        const matchesAcademicYear = !academicYearFilter || academicYearCell.includes(academicYearFilter);
        
        row.style.display = (matchesTeacher && matchesClass && matchesSubject && matchesCycle && matchesAcademicYear) ? '' : 'none';
    });
}

function deleteAssignment(assignmentId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette assignation ?')) {
        fetch(`<?= base_url('admin/etudes/assignments/delete/') ?>${assignmentId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la suppression: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la suppression');
        });
    }
}

// Fonction de tri du tableau
function sortTable(columnIndex) {
    const table = document.querySelector('table');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Déterminer la direction de tri
    const currentDirection = table.dataset.sortDirection === 'asc' ? 'desc' : 'asc';
    table.dataset.sortDirection = currentDirection;
    
    // Mettre à jour les icônes de tri
    updateSortIcons(columnIndex, currentDirection);
    
    // Trier les lignes
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();
        
        let comparison = 0;
        
        // Gestion spéciale pour les colonnes avec des tags ou des éléments complexes
        if (columnIndex === 5) { // Colonne Statut
            const aStatus = aValue.includes('Actif') ? 'Actif' : 'Inactif';
            const bStatus = bValue.includes('Actif') ? 'Actif' : 'Inactif';
            comparison = aStatus.localeCompare(bStatus, 'fr');
        } else {
            comparison = aValue.localeCompare(bValue, 'fr');
        }
        
        return currentDirection === 'asc' ? comparison : -comparison;
    });
    
    // Réorganiser les lignes dans le tableau
    rows.forEach(row => tbody.appendChild(row));
}

// Mettre à jour les icônes de tri
function updateSortIcons(activeColumn, direction) {
    const headers = document.querySelectorAll('th');
    headers.forEach((header, index) => {
        const icon = header.querySelector('.icon i');
        if (icon) {
            if (index === activeColumn) {
                icon.className = direction === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
            } else {
                icon.className = 'fas fa-sort';
            }
        }
    });
}
</script>

<?= $this->endSection() ?>
