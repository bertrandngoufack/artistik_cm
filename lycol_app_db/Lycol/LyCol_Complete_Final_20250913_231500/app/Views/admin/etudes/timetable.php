<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="section">
        <!-- En-tête -->
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <h1 class="title">Gestion des Emplois du Temps</h1>
                </div>
            </div>
            <div class="level-right">
                <div class="level-item">
                    <a href="<?= base_url('admin/etudes/timetable/print') ?>" class="button is-info mr-2">
                        <span class="icon">
                            <i class="fas fa-print"></i>
                        </span>
                        <span>Imprimer EDT</span>
                    </a>
                    <a href="<?= base_url('admin/etudes/timetable/create') ?>" class="button is-primary">
                        <span class="icon">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span>Nouvel Emploi du Temps</span>
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
                                    <p class="heading has-text-white">Total EDT</p>
                                    <p class="title has-text-white"><?= $total_timetables ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="level-right">
                            <div class="level-item">
                                <span class="icon has-text-white">
                                    <i class="fas fa-calendar-alt"></i>
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
                <div class="box has-background-success has-text-white">
                    <div class="level">
                        <div class="level-left">
                            <div class="level-item">
                                <div>
                                    <p class="heading has-text-white">Enseignants</p>
                                    <p class="title has-text-white"><?= $teachers_involved ?? 0 ?></p>
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
                        <label class="label">Classe</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select id="class_filter">
                                    <option value="">Toutes les classes</option>
                                    <?php if (isset($classes) && is_array($classes)): ?>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?= $class['id'] ?>"><?= esc($class['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Enseignant</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select id="teacher_filter">
                                    <option value="">Tous les enseignants</option>
                                    <?php if (isset($teachers) && is_array($teachers)): ?>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <option value="<?= $teacher['id'] ?>">
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
                        <label class="label">Matière</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select id="subject_filter">
                                    <option value="">Toutes les matières</option>
                                    <?php if (isset($subjects) && is_array($subjects)): ?>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?= $subject['id'] ?>"><?= esc($subject['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Jour</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select id="day_filter">
                                    <option value="">Tous les jours</option>
                                    <option value="1">Lundi</option>
                                    <option value="2">Mardi</option>
                                    <option value="3">Mercredi</option>
                                    <option value="4">Jeudi</option>
                                    <option value="5">Vendredi</option>
                                    <option value="6">Samedi</option>
                                    <option value="7">Dimanche</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-12">
                    <div class="field">
                        <div class="control">
                            <button class="button is-info is-small" onclick="resetFilters()">
                                <span class="icon">
                                    <i class="fas fa-refresh"></i>
                                </span>
                                <span>Réinitialiser les filtres</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des emplois du temps -->
        <div class="box">
            <div class="table-container">
                <table class="table is-fullwidth is-striped is-hoverable">
                    <thead>
                        <tr>
                            <th>Classe</th>
                            <th>Matière</th>
                            <th>Enseignant</th>
                            <th>Jour</th>
                            <th>Horaire</th>
                            <th>Durée</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($timetables) && is_array($timetables)): ?>
                            <?php foreach ($timetables as $timetable): ?>
                                <tr>
                                    <td>
                                        <span class="tag is-info">
                                            <?= esc($timetable['class_name'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= esc($timetable['subject_name'] ?? 'N/A') ?></strong>
                                    </td>
                                    <td>
                                        <?= esc($timetable['teacher_name'] ?: 'N/A') ?>
                                    </td>
                                    <td>
                                        <?php
                                        $days = ['', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
                                        $dayName = $days[$timetable['day_of_week']] ?? 'N/A';
                                        ?>
                                        <span class="tag is-light"><?= $dayName ?></span>
                                    </td>
                                    <td>
                                        <span class="has-text-weight-semibold">
                                            <?= esc($timetable['start_time']) ?> - <?= esc($timetable['end_time']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $start = new DateTime($timetable['start_time']);
                                        $end = new DateTime($timetable['end_time']);
                                        $duration = $start->diff($end);
                                        $hours = $duration->h;
                                        $minutes = $duration->i;
                                        $durationText = $hours > 0 ? "{$hours}h" : "";
                                        $durationText .= $minutes > 0 ? "{$minutes}min" : "";
                                        ?>
                                        <span class="tag is-warning"><?= $durationText ?></span>
                                    </td>
                                    <td>
                                        <div class="buttons are-small">
                                            <a href="<?= base_url('admin/etudes/timetable/class/' . $timetable['class_id']) ?>" 
                                               class="button is-info" title="Voir emploi du temps de la classe">
                                                <span class="icon">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                            </a>
                                            <a href="<?= base_url('admin/etudes/timetable/' . $timetable['id'] . '/edit') ?>" 
                                               class="button is-warning" title="Modifier">
                                                <span class="icon">
                                                    <i class="fas fa-edit"></i>
                                                </span>
                                            </a>
                                            <a href="<?= base_url('admin/etudes/timetable/' . $timetable['id'] . '/delete') ?>" 
                                               class="button is-danger" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet emploi du temps ?')"
                                               title="Supprimer">
                                                <span class="icon">
                                                    <i class="fas fa-trash"></i>
                                                </span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="has-text-centered">
                                    <p class="has-text-grey">Aucun emploi du temps trouvé</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Vue d'ensemble par classe -->
        <?php if (isset($timetables) && !empty($timetables)): ?>
        <div class="box">
            <h3 class="title is-4">Vue d'ensemble par classe</h3>
            <div class="columns is-multiline">
                <?php
                $classesTimetables = [];
                foreach ($timetables as $timetable) {
                    $classId = $timetable['class_id'];
                    if (!isset($classesTimetables[$classId])) {
                        $classesTimetables[$classId] = [
                            'name' => $timetable['class_name'],
                            'timetables' => []
                        ];
                    }
                    $classesTimetables[$classId]['timetables'][] = $timetable;
                }
                ?>
                
                <?php foreach ($classesTimetables as $classId => $classData): ?>
                <div class="column is-6">
                    <div class="card">
                        <header class="card-header">
                            <p class="card-header-title">
                                <?= esc($classData['name']) ?>
                            </p>
                        </header>
                        <div class="card-content">
                            <div class="content">
                                <?php
                                $days = ['', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
                                $dayTimetables = [];
                                foreach ($classData['timetables'] as $timetable) {
                                    $day = $timetable['day_of_week'];
                                    if (!isset($dayTimetables[$day])) {
                                        $dayTimetables[$day] = [];
                                    }
                                    $dayTimetables[$day][] = $timetable;
                                }
                                ?>
                                
                                <?php for ($day = 1; $day <= 5; $day++): ?>
                                    <?php if (isset($dayTimetables[$day])): ?>
                                        <div class="mb-3">
                                            <strong><?= $days[$day] ?>:</strong>
                                            <?php foreach ($dayTimetables[$day] as $timetable): ?>
                                                <span class="tag is-small is-info mr-1">
                                                    <?= esc($timetable['subject_name']) ?> 
                                                    (<?= esc($timetable['start_time']) ?>)
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endfor; ?>
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
// Attendre que le DOM soit complètement chargé
document.addEventListener('DOMContentLoaded', function() {
    // Filtres
    const classFilter = document.getElementById('class_filter');
    const teacherFilter = document.getElementById('teacher_filter');
    const subjectFilter = document.getElementById('subject_filter');
    const dayFilter = document.getElementById('day_filter');
    
    if (classFilter) classFilter.addEventListener('change', filterTimetables);
    if (teacherFilter) teacherFilter.addEventListener('change', filterTimetables);
    if (subjectFilter) subjectFilter.addEventListener('change', filterTimetables);
    if (dayFilter) dayFilter.addEventListener('change', filterTimetables);
    
    console.log('Filtres initialisés avec succès');
});

function filterTimetables() {
    console.log('Fonction filterTimetables appelée');
    
    const classFilter = document.getElementById('class_filter').value;
    const teacherFilter = document.getElementById('teacher_filter').value;
    const subjectFilter = document.getElementById('subject_filter').value;
    const dayFilter = document.getElementById('day_filter').value;
    
    console.log('Filtres actuels:', { classFilter, teacherFilter, subjectFilter, dayFilter });
    
    const rows = document.querySelectorAll('tbody tr');
    console.log('Lignes trouvées:', rows.length);
    
    let visibleCount = 0;
    
    rows.forEach((row, index) => {
        // Récupérer les données des cellules
        const classCell = row.cells[0] ? row.cells[0].textContent.trim() : '';
        const subjectCell = row.cells[1] ? row.cells[1].textContent.trim() : '';
        const teacherCell = row.cells[2] ? row.cells[2].textContent.trim() : '';
        const dayCell = row.cells[3] ? row.cells[3].textContent.trim() : '';
        
        console.log(`Ligne ${index}:`, { classCell, subjectCell, teacherCell, dayCell });
        
        // Filtrage amélioré
        let matchesClass = true;
        let matchesSubject = true;
        let matchesTeacher = true;
        let matchesDay = true;
        
        // Filtre par classe
        if (classFilter) {
            const selectedClassOption = document.querySelector('#class_filter option[value="' + classFilter + '"]');
            const selectedClassName = selectedClassOption ? selectedClassOption.textContent.trim() : '';
            matchesClass = classCell.includes(selectedClassName);
            console.log(`Filtre classe: '${selectedClassName}' dans '${classCell}' = ${matchesClass}`);
        }
        
        // Filtre par matière
        if (subjectFilter) {
            const selectedSubjectOption = document.querySelector('#subject_filter option[value="' + subjectFilter + '"]');
            const selectedSubjectName = selectedSubjectOption ? selectedSubjectOption.textContent.trim() : '';
            matchesSubject = subjectCell.includes(selectedSubjectName);
            console.log(`Filtre matière: '${selectedSubjectName}' dans '${subjectCell}' = ${matchesSubject}`);
        }
        
        // Filtre par enseignant
        if (teacherFilter) {
            const selectedTeacherOption = document.querySelector('#teacher_filter option[value="' + teacherFilter + '"]');
            const selectedTeacherName = selectedTeacherOption ? selectedTeacherOption.textContent.trim() : '';
            matchesTeacher = teacherCell.includes(selectedTeacherName);
            console.log(`Filtre enseignant: '${selectedTeacherName}' dans '${teacherCell}' = ${matchesTeacher}`);
        }
        
        // Filtre par jour
        if (dayFilter) {
            const days = ['', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
            const selectedDayName = days[parseInt(dayFilter)] || '';
            matchesDay = dayCell.includes(selectedDayName);
            console.log(`Filtre jour: '${selectedDayName}' dans '${dayCell}' = ${matchesDay}`);
        }
        
        // Afficher/masquer la ligne
        const shouldShow = matchesClass && matchesSubject && matchesTeacher && matchesDay;
        row.style.display = shouldShow ? '' : 'none';
        
        if (shouldShow) {
            visibleCount++;
        }
        
        console.log(`Ligne ${index} - shouldShow: ${shouldShow}, display: ${row.style.display}`);
    });
    
    console.log('Lignes visibles:', visibleCount);
    
    // Mettre à jour le compteur d'éléments visibles
    updateVisibleCount(visibleCount);
}

function updateVisibleCount(count) {
    // Mettre à jour les statistiques en temps réel
    const totalElement = document.querySelector('.box.has-background-primary .title');
    if (totalElement) {
        totalElement.textContent = count;
    }
}

function resetFilters() {
    // Réinitialiser tous les filtres
    document.getElementById('class_filter').value = '';
    document.getElementById('teacher_filter').value = '';
    document.getElementById('subject_filter').value = '';
    document.getElementById('day_filter').value = '';
    
    // Afficher toutes les lignes
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        row.style.display = '';
    });
    
    // Mettre à jour le compteur
    updateVisibleCount(rows.length);
}

// Fonction de suppression supprimée - remplacée par des liens directs
</script>

<?= $this->endSection() ?>
