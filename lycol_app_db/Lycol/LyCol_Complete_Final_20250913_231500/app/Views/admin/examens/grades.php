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
                    <li class="is-active"><a href="#" aria-current="page">Gestion des Notes</a></li>
                </ul>
            </nav>

            <!-- Header -->
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <h1 class="title">Gestion des Notes</h1>
                    </div>
                </div>
            </div>

            <!-- Liste des examens pour saisie de notes -->
            <div class="box">
                <h3 class="title is-5">
                    <span class="icon">
                        <i class="fas fa-edit"></i>
                    </span>
                    Examens pour Saisie de Notes
                </h3>
                
                <div class="table-container">
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Examen</th>
                                <th>Classe</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Notes Saisies</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($exams)): ?>
                                <?php foreach ($exams as $exam): ?>
                                <tr>
                                    <td><?= esc($exam['name']) ?></td>
                                    <td><?= esc($exam['class_name'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="tag is-info"><?= esc($exam['exam_type_translated']) ?></span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($exam['exam_date'])) ?></td>
                                    <td>
                                        <?php
                                        $statusClass = 'is-info';
                                        switch ($exam['status']) {
                                            case 'COMPLETED':
                                                $statusClass = 'is-success';
                                                break;
                                            case 'IN_PROGRESS':
                                                $statusClass = 'is-warning';
                                                break;
                                            case 'CANCELLED':
                                                $statusClass = 'is-danger';
                                                break;
                                        }
                                        ?>
                                        <span class="tag <?= $statusClass ?>"><?= esc($exam['status']) ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $gradesCount = $exam['grades_count'] ?? 0;
                                        $totalStudents = $exam['total_students'] ?? 0;
                                        $percentage = $totalStudents > 0 ? round(($gradesCount / $totalStudents) * 100, 1) : 0;
                                        ?>
                                        <span class="tag <?= $percentage == 100 ? 'is-success' : ($percentage > 0 ? 'is-warning' : 'is-danger') ?>">
                                            <?= $gradesCount ?>/<?= $totalStudents ?> (<?= $percentage ?>%)
                                        </span>
                                    </td>
                                    <td>
                                        <div class="buttons are-small">
                                            <a href="<?= base_url('admin/examens/grades/enter/' . $exam['id']) ?>" class="button is-success">
                                                <span class="icon">
                                                    <i class="fas fa-edit"></i>
                                                </span>
                                                <span>Saisir Notes</span>
                                            </a>
                                            <a href="<?= base_url('admin/examens/grades/view/' . $exam['id']) ?>" class="button is-info">
                                                <span class="icon">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                                <span>Voir Notes</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="has-text-centered">
                                        <p class="has-text-grey">Aucun examen disponible pour la saisie de notes</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Statistiques des notes -->
            <div class="columns">
                <div class="column is-4">
                    <div class="box has-text-centered">
                        <p class="heading">Total Notes Saisies</p>
                        <p class="title"><?= number_format($stats['totalGrades'] ?? 0) ?></p>
                    </div>
                </div>
                <div class="column is-4">
                    <div class="box has-text-centered">
                        <p class="heading">Moyenne Générale</p>
                        <p class="title"><?= number_format($stats['averageScore'] ?? 0, 2) ?>/20</p>
                    </div>
                </div>
                <div class="column is-4">
                    <div class="box has-text-centered">
                        <p class="heading">Taux de Réussite</p>
                        <p class="title"><?= number_format($stats['passRate'] ?? 0, 1) ?>%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
