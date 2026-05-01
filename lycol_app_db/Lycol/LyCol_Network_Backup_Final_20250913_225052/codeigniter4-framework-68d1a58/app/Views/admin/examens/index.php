<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Module Examens</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/examens/add') ?>" class="button is-primary">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>Nouvel Examen</span>
            </a>
        </div>
    </div>
</div>

<div class="columns is-multiline">
    <!-- Statistiques -->
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Total Examens</p>
                    <p class="title"><?= number_format($total_exams) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Total Notes</p>
                    <p class="title"><?= number_format($total_grades) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Moyenne Générale</p>
                    <p class="title"><?= number_format($exam_stats->average ?? 0, 2) ?>/20</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Taux de Réussite</p>
                    <p class="title">85%</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Examens récents -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-calendar-check"></i></span>
            Examens Récents
        </p>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped">
                <thead>
                    <tr>
                        <th>Examen</th>
                        <th>Classe</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_exams)): ?>
                        <?php foreach ($recent_exams as $exam): ?>
                        <tr>
                            <td><?= esc($exam['name']) ?></td>
                            <td><?= esc($exam['class_name'] ?? 'N/A') ?></td>
                            <td>
                                <span class="tag is-info"><?= esc($exam['exam_type']) ?></span>
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
                                <div class="buttons are-small">
                                    <a href="<?= base_url('admin/examens/exam/' . $exam['id']) ?>" class="button is-info">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/examens/grades/' . $exam['id']) ?>" class="button is-success">
                                        <span class="icon"><i class="fas fa-edit"></i></span>
                                        <span>Notes</span>
                                    </a>
                                    <a href="<?= base_url('admin/examens/report/' . $exam['id']) ?>" class="button is-warning">
                                        <span class="icon"><i class="fas fa-file-alt"></i></span>
                                        <span>Rapport</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="has-text-centered">
                                <p class="has-text-grey">Aucun examen programmé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Actions rapides -->
<div class="columns">
    <div class="column">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-tasks"></i></span>
                    Actions Rapides
                </p>
            </header>
            <div class="card-content">
                <div class="buttons">
                    <a href="<?= base_url('admin/examens/list') ?>" class="button is-primary">
                        <span class="icon"><i class="fas fa-list"></i></span>
                        <span>Tous les Examens</span>
                    </a>
                    <a href="<?= base_url('admin/examens/grades') ?>" class="button is-success">
                        <span class="icon"><i class="fas fa-edit"></i></span>
                        <span>Saisie des Notes</span>
                    </a>
                    <a href="<?= base_url('admin/examens/bulletins') ?>" class="button is-info">
                        <span class="icon"><i class="fas fa-file-alt"></i></span>
                        <span>Bulletins</span>
                    </a>
                    <a href="<?= base_url('admin/examens/statistics') ?>" class="button is-warning">
                        <span class="icon"><i class="fas fa-chart-bar"></i></span>
                        <span>Statistiques</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
