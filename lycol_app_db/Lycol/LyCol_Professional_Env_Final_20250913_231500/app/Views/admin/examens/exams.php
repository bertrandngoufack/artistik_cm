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
                    <li class="is-active"><a href="#" aria-current="page">Gestion des Examens</a></li>
                </ul>
            </nav>

            <!-- Header -->
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <h1 class="title">Gestion des Examens</h1>
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

            <!-- Liste des examens -->
            <div class="box">
                <div class="table-container">
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Nom de l'Examen</th>
                                <th>Classe</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Note Max</th>
                                <th>Statut</th>
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
                                    <td><?= esc($exam['total_marks']) ?></td>
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
                                            <a href="<?= base_url('admin/examens/exams/' . $exam['id'] . '/edit') ?>" class="button is-warning">
                                                <span class="icon">
                                                    <i class="fas fa-edit"></i>
                                                </span>
                                            </a>
                                            <a href="<?= base_url('admin/examens/grades/enter/' . $exam['id']) ?>" class="button is-success">
                                                <span class="icon">
                                                    <i class="fas fa-edit"></i>
                                                </span>
                                                <span>Notes</span>
                                            </a>
                                            <a href="<?= base_url('admin/examens/exams/' . $exam['id'] . '/delete') ?>" 
                                               class="button is-danger"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet examen ?')">
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
                                        <p class="has-text-grey">Aucun examen trouvé</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($pager) && $pager): ?>
                    <div class="has-text-centered mt-4">
                        <?= $pager->links() ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
