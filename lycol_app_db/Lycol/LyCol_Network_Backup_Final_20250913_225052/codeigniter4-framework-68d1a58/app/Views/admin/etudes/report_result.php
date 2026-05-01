<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <!-- En-tête -->
    <div class="level mb-5">
        <div class="level-left">
            <div class="level-item">
                <h1 class="title is-2"><?= $title ?></h1>
                <p class="subtitle is-5 has-text-grey">Rapport généré le <?= date('d/m/Y à H:i:s') ?></p>
            </div>
        </div>
        <div class="level-right">
            <div class="level-item">
                <a href="<?= base_url('admin/etudes/reports') ?>" class="button is-light">
                    <span class="icon"><i class="fas fa-arrow-left"></i></span>
                    <span>Retour aux rapports</span>
                </a>
                <a href="<?= base_url('admin/etudes/reports/export/csv?report_type=' . $reportType . '&cycle_id=' . $cycleId . '&class_id=' . $classId . '&subject_id=' . $subjectId . '&teacher_id=' . $teacherId . '&academic_year=' . $academicYear) ?>" class="button is-success ml-2">
                    <span class="icon"><i class="fas fa-download"></i></span>
                    <span>Exporter CSV</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Filtres appliqués -->
    <?php if ($cycleId || $classId || $subjectId || $teacherId): ?>
    <div class="notification is-info is-light mb-5">
        <h4 class="title is-5">Filtres appliqués :</h4>
        <div class="tags">
            <?php if ($filters['cycle']): ?>
                <span class="tag is-info">Cycle: <?= esc($filters['cycle']['name']) ?></span>
            <?php endif; ?>
            <?php if ($filters['class']): ?>
                <span class="tag is-success">Classe: <?= esc($filters['class']['name']) ?></span>
            <?php endif; ?>
            <?php if ($filters['subject']): ?>
                <span class="tag is-warning">Matière: <?= esc($filters['subject']['name']) ?></span>
            <?php endif; ?>
            <?php if ($filters['teacher']): ?>
                <span class="tag is-danger">Enseignant: <?= esc($filters['teacher']['first_name'] . ' ' . $filters['teacher']['last_name']) ?></span>
            <?php endif; ?>
            <span class="tag is-primary">Année: <?= esc($academicYear) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Contenu du rapport -->
    <?php if ($reportType === 'summary'): ?>
        <!-- Rapport général -->
        <div class="columns is-multiline">
            <div class="column is-12">
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            <span class="icon mr-2">
                                <i class="fas fa-chart-pie"></i>
                            </span>
                            Vue d'ensemble
                        </p>
                    </header>
                    <div class="card-content">
                        <div class="columns is-multiline">
                            <div class="column is-3">
                                <div class="box has-background-primary has-text-white">
                                    <div class="has-text-centered">
                                        <h4 class="title is-5 has-text-white">Total Cycles</h4>
                                        <p class="title is-2 has-text-white"><?= count($reportData['cycles']) ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="column is-3">
                                <div class="box has-background-success has-text-white">
                                    <div class="has-text-centered">
                                        <h4 class="title is-5 has-text-white">Total Classes</h4>
                                        <p class="title is-2 has-text-white"><?= count($reportData['classes']) ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="column is-3">
                                <div class="box has-background-info has-text-white">
                                    <div class="has-text-centered">
                                        <h4 class="title is-5 has-text-white">Total Matières</h4>
                                        <p class="title is-2 has-text-white"><?= count($reportData['subjects']) ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="column is-3">
                                <div class="box has-background-warning has-text-white">
                                    <div class="has-text-centered">
                                        <h4 class="title is-5 has-text-white">Total Assignations</h4>
                                        <p class="title is-2 has-text-white"><?= count($reportData['assignments']) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Détails par cycle -->
            <div class="column is-6">
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            <span class="icon mr-2">
                                <i class="fas fa-layer-group"></i>
                            </span>
                            Répartition par cycle
                        </p>
                    </header>
                    <div class="card-content">
                        <table class="table is-fullwidth">
                            <thead>
                                <tr>
                                    <th>Cycle</th>
                                    <th>Classes</th>
                                    <th>Élèves</th>
                                    <th>Enseignants</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportData['cycles'] as $cycle): ?>
                                <tr>
                                    <td><?= esc($cycle['cycle']['name']) ?></td>
                                    <td><?= $cycle['classes_count'] ?></td>
                                    <td><?= $cycle['students_count'] ?></td>
                                    <td><?= $cycle['teachers_count'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Détails par classe -->
            <div class="column is-6">
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            <span class="icon mr-2">
                                <i class="fas fa-chalkboard"></i>
                            </span>
                            Répartition par classe
                        </p>
                    </header>
                    <div class="card-content">
                        <table class="table is-fullwidth">
                            <thead>
                                <tr>
                                    <th>Classe</th>
                                    <th>Élèves</th>
                                    <th>Enseignants</th>
                                    <th>Matières</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($reportData['classes'], 0, 10) as $class): ?>
                                <tr>
                                    <td><?= esc($class['class']['name']) ?></td>
                                    <td><?= $class['students_count'] ?></td>
                                    <td><?= $class['teachers_count'] ?></td>
                                    <td><?= $class['subjects_count'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($reportType === 'assignments'): ?>
        <!-- Rapport des assignations -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon mr-2">
                        <i class="fas fa-user-tie"></i>
                    </span>
                    Assignations des enseignants
                </p>
            </header>
            <div class="card-content">
                <?php if (!empty($reportData)): ?>
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Enseignant</th>
                                <th>Classe</th>
                                <th>Matière</th>
                                <th>Principal</th>
                                <th>Année</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportData as $assignment): ?>
                            <tr>
                                <td><?= esc($assignment['first_name'] . ' ' . $assignment['last_name']) ?></td>
                                <td><?= esc($assignment['class_name']) ?></td>
                                <td><?= esc($assignment['subject_name']) ?></td>
                                <td>
                                    <?php if ($assignment['is_principal']): ?>
                                        <span class="tag is-success">Oui</span>
                                    <?php else: ?>
                                        <span class="tag is-light">Non</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($assignment['academic_year']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="has-text-centered py-5">
                        <span class="icon is-large has-text-grey-light">
                            <i class="fas fa-info-circle"></i>
                        </span>
                        <p class="has-text-grey-light mt-2">Aucune assignation trouvée avec les critères sélectionnés</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($reportType === 'cycles'): ?>
        <!-- Rapport par cycle -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon mr-2">
                        <i class="fas fa-layer-group"></i>
                    </span>
                    Rapport par cycle
                </p>
            </header>
            <div class="card-content">
                <?php if (!empty($reportData)): ?>
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Cycle</th>
                                <th>Classes</th>
                                <th>Élèves</th>
                                <th>Enseignants</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportData as $cycle): ?>
                            <tr>
                                <td><?= esc($cycle['cycle']['name']) ?></td>
                                <td><?= $cycle['classes_count'] ?></td>
                                <td><?= $cycle['students_count'] ?></td>
                                <td><?= $cycle['teachers_count'] ?></td>
                                <td>
                                    <a href="<?= base_url('admin/etudes/reports/generate?report_type=classes&cycle_id=' . $cycle['cycle']['id'] . '&format=html') ?>" class="button is-small is-info">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                        <span>Voir classes</span>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="has-text-centered py-5">
                        <span class="icon is-large has-text-grey-light">
                            <i class="fas fa-info-circle"></i>
                        </span>
                        <p class="has-text-grey-light mt-2">Aucun cycle trouvé</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($reportType === 'classes'): ?>
        <!-- Rapport par classe -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon mr-2">
                        <i class="fas fa-chalkboard"></i>
                    </span>
                    Rapport par classe
                </p>
            </header>
            <div class="card-content">
                <?php if (!empty($reportData)): ?>
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Classe</th>
                                <th>Cycle</th>
                                <th>Élèves</th>
                                <th>Enseignants</th>
                                <th>Matières</th>
                                <th>Heures EDT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportData as $class): ?>
                            <tr>
                                <td><?= esc($class['class']['name']) ?></td>
                                <td><?= esc($class['class']['cycle_name'] ?? 'N/A') ?></td>
                                <td><?= $class['students_count'] ?></td>
                                <td><?= $class['teachers_count'] ?></td>
                                <td><?= $class['subjects_count'] ?></td>
                                <td><?= $class['timetable_hours'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="has-text-centered py-5">
                        <span class="icon is-large has-text-grey-light">
                            <i class="fas fa-info-circle"></i>
                        </span>
                        <p class="has-text-grey-light mt-2">Aucune classe trouvée</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>
        <!-- Autres types de rapports -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon mr-2">
                        <i class="fas fa-file-alt"></i>
                    </span>
                    <?= ucfirst($reportType) ?>
                </p>
            </header>
            <div class="card-content">
                <div class="has-text-centered py-5">
                    <span class="icon is-large has-text-grey-light">
                        <i class="fas fa-info-circle"></i>
                    </span>
                    <p class="has-text-grey-light mt-2">Rapport de type "<?= esc($reportType) ?>" en cours de développement</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.card.has-background-primary,
.card.has-background-success,
.card.has-background-info,
.card.has-background-warning,
.card.has-background-danger {
    transition: transform 0.2s ease-in-out;
}

.card.has-background-primary:hover,
.card.has-background-success:hover,
.card.has-background-info:hover,
.card.has-background-warning:hover,
.card.has-background-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.table th {
    background-color: #f5f5f5;
    font-weight: 600;
}

.notification .tags .tag {
    margin-right: 0.5rem;
    margin-bottom: 0.25rem;
}
</style>

<?= $this->endSection() ?>



















