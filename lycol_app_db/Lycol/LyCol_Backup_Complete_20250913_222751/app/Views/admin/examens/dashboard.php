<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column is-12">
            <!-- Breadcrumb -->
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Module Examens</a></li>
                </ul>
            </nav>

            <!-- Filtre année scolaire -->
            <div class="box">
                <div class="level">
                    <div class="level-left">
                        <div class="level-item">
                            <div class="field">
                                <label class="label">Année Académique</label>
                                <div class="control">
                                    <div class="select">
                                        <select id="academic-year-filter" onchange="filterByAcademicYear()">
                                            <?php foreach ($available_academic_years as $year): ?>
                                            <option value="<?= $year ?>" <?= $year === $current_academic_year ? 'selected' : '' ?>>
                                                <?= $year ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="level-right">
                        <div class="level-item">
                            <p class="has-text-grey">
                                <strong>Année Académique <?= $current_academic_year ?></strong> :
                                Du <?= date('d/m/Y', strtotime($academic_year_start)) ?> au <?= date('d/m/Y', strtotime($academic_year_end)) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header -->
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <h1 class="title">Module Examens</h1>
                    </div>
                </div>
                <div class="level-right">
                    <div class="level-item">
                        <a href="<?= base_url('admin/examens/exams/create') ?>" class="button is-primary">
                            <span class="icon">
                                <i class="fas fa-plus"></i>
                            </span>
                            <span>Nouvel Examen</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="columns is-multiline">
                <div class="column is-3">
                    <div class="box has-text-centered">
                        <p class="heading">Total Examens</p>
                        <p class="title"><?= number_format($stats['totalExams'] ?? 0) ?></p>
                    </div>
                </div>
                
                <div class="column is-3">
                    <div class="box has-text-centered">
                        <p class="heading">Total Notes</p>
                        <p class="title"><?= number_format($stats['totalGrades'] ?? 0) ?></p>
                    </div>
                </div>
                
                <div class="column is-3">
                    <div class="box has-text-centered">
                        <p class="heading">Moyenne Générale</p>
                        <p class="title"><?= number_format($stats['averageScore'] ?? 0, 2) ?>/20</p>
                    </div>
                </div>
                
                <div class="column is-3">
                    <div class="box has-text-centered">
                        <p class="heading">Taux de Réussite</p>
                        <p class="title"><?= number_format($stats['passRate'] ?? 85, 1) ?>%</p>
                    </div>
                </div>
            </div>

            <!-- Examens récents -->
            <div class="box">
                <h3 class="title is-5">
                    <span class="icon">
                        <i class="fas fa-calendar-check"></i>
                    </span>
                    Examens Récents
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentExams)): ?>
                                <?php foreach ($recentExams as $exam): ?>
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
                                            case 'TERMINÉ':
                                                $statusClass = 'is-success';
                                                break;
                                            case 'EN_COURS':
                                                $statusClass = 'is-warning';
                                                break;
                                            case 'ANNULE':
                                                $statusClass = 'is-danger';
                                                break;
                                            case 'PROGRAMMÉ':
                                                $statusClass = 'is-info';
                                                break;
                                        }
                                        ?>
                                        <span class="tag <?= $statusClass ?>"><?= esc($exam['status_translated']) ?></span>
                                    </td>
                                    <td>
                                        <div class="buttons are-small">
                                            <a href="<?= base_url('admin/examens/exams/' . $exam['id'] . '/view') ?>" class="button is-info">
                                                <span class="icon">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                            </a>
                                            <a href="<?= base_url('admin/examens/grades/enter/' . $exam['id']) ?>" class="button is-success">
                                                <span class="icon">
                                                    <i class="fas fa-edit"></i>
                                                </span>
                                                <span>Notes</span>
                                            </a>
                                            <a href="<?= base_url('admin/examens/report-cards/generate?exam_id=' . $exam['id']) ?>" class="button is-warning">
                                                <span class="icon">
                                                    <i class="fas fa-file-alt"></i>
                                                </span>
                                                <span>Bulletin</span>
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

            <!-- Actions rapides -->
            <div class="box">
                <h3 class="title is-5">
                    <span class="icon">
                        <i class="fas fa-tasks"></i>
                    </span>
                    Actions Rapides
                </h3>
                
                <div class="buttons">
                    <a href="<?= base_url('admin/examens/exams') ?>" class="button is-primary">
                        <span class="icon">
                            <i class="fas fa-list"></i>
                        </span>
                        <span>Tous les Examens</span>
                    </a>
                    <a href="<?= base_url('admin/examens/grades') ?>" class="button is-success">
                        <span class="icon">
                            <i class="fas fa-edit"></i>
                        </span>
                        <span>Saisie des Notes</span>
                    </a>
                    <a href="<?= base_url('admin/examens/report-cards') ?>" class="button is-info">
                        <span class="icon">
                            <i class="fas fa-file-alt"></i>
                        </span>
                        <span>Bulletins</span>
                    </a>
                    <a href="<?= base_url('admin/examens/academic-periods') ?>" class="button is-warning">
                        <span class="icon">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                        <span>Périodes Académiques</span>
                    </a>
                    <a href="<?= base_url('admin/examens/statistics') ?>" class="button is-warning">
                        <span class="icon">
                            <i class="fas fa-chart-bar"></i>
                        </span>
                        <span>Statistiques</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<script>
function filterByAcademicYear() {
    const academicYear = document.getElementById('academic-year-filter').value;
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('academic_year', academicYear);
    window.location.href = currentUrl.toString();
}
</script>
