<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container exam-details-page">
    <div class="columns">
        <div class="column is-12">
            <!-- En-tête -->
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <div>
                            <h1 class="title">
                                <span class="icon">
                                    <i class="fas fa-eye"></i>
                                </span>
                                Détails de l'Examen
                            </h1>
                            <p class="subtitle">Informations complètes sur l'examen</p>
                        </div>
                    </div>
                </div>
                <div class="level-right">
                    <div class="level-item">
                        <div class="buttons">
                            <a href="<?= base_url('admin/examens/exams/' . $exam['id'] . '/edit') ?>" class="button is-warning">
                                <span class="icon">
                                    <i class="fas fa-edit"></i>
                                </span>
                                <span>Modifier</span>
                            </a>
                            <a href="<?= base_url('admin/examens/grades/enter/' . $exam['id']) ?>" class="button is-success">
                                <span class="icon">
                                    <i class="fas fa-edit"></i>
                                </span>
                                <span>Saisir Notes</span>
                            </a>
                            <a href="<?= base_url('admin/examens/exams') ?>" class="button is-light">
                                <span class="icon">
                                    <i class="fas fa-arrow-left"></i>
                                </span>
                                <span>Retour</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations de l'examen -->
            <div class="box">
                <h3 class="title is-5">
                    <span class="icon">
                        <i class="fas fa-info-circle"></i>
                    </span>
                    Informations Générales
                </h3>
                
                <div class="columns">
                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Nom de l'Examen</label>
                            <div class="control">
                                <p class="has-text-weight-semibold"><?= esc($exam['name']) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Classe</label>
                            <div class="control">
                                <p class="has-text-weight-semibold"><?= esc($exam['class_name'] ?? 'N/A') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="columns">
                    <div class="column is-4">
                        <div class="field">
                            <label class="label">Type d'Examen</label>
                            <div class="control">
                                <span class="tag is-info"><?= esc($exam['exam_type_translated']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="column is-4">
                        <div class="field">
                            <label class="label">Date de l'Examen</label>
                            <div class="control">
                                <p class="has-text-weight-semibold"><?= date('d/m/Y', strtotime($exam['exam_date'])) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="column is-4">
                        <div class="field">
                            <label class="label">Note Maximale</label>
                            <div class="control">
                                <p class="has-text-weight-semibold"><?= esc($exam['total_marks']) ?>/20</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="columns">
                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Statut</label>
                            <div class="control">
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
                                <span class="tag <?= $statusClass ?>"><?= esc($exam['status_translated']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Coefficient</label>
                            <div class="control">
                                <p class="has-text-weight-semibold"><?= esc($exam['coefficient'] ?? 1.00) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques des notes -->
            <?php if (!empty($grades)): ?>
            <div class="box">
                <h3 class="title is-5">
                    <span class="icon">
                        <i class="fas fa-chart-bar"></i>
                    </span>
                    Statistiques des Notes
                </h3>
                
                <div class="columns">
                    <div class="column is-3">
                        <div class="box has-text-centered">
                            <p class="heading">Total Notes</p>
                            <p class="title"><?= $stats['total_grades'] ?></p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="box has-text-centered">
                            <p class="heading">Moyenne</p>
                            <p class="title"><?= $stats['average'] ?>/20</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="box has-text-centered">
                            <p class="heading">Réussis</p>
                            <p class="title"><?= $stats['passed'] ?></p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="box has-text-centered">
                            <p class="heading">Taux de Réussite</p>
                            <p class="title"><?= $stats['pass_rate'] ?>%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des notes -->
            <div class="box">
                <h3 class="title is-5">
                    <span class="icon">
                        <i class="fas fa-list"></i>
                    </span>
                    Détail des Notes
                </h3>
                
                <div class="table-container">
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Élève</th>
                                <th>Note</th>
                                <th>Pourcentage</th>
                                <th>Statut</th>
                                <th>Commentaires</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grades as $grade): ?>
                            <tr>
                                <td>
                                    <?php
                                    $student = null;
                                    foreach ($students as $s) {
                                        if ($s['id'] == $grade['student_id']) {
                                            $student = $s;
                                            break;
                                        }
                                    }
                                    ?>
                                    <?php if ($student): ?>
                                        <div class="media">
                                            <div class="media-content">
                                                <p class="has-text-weight-semibold"><?= esc($student['first_name'] . ' ' . $student['last_name']) ?></p>
                                                <p class="is-size-7 has-text-grey">Matricule: <?= esc($student['matricule']) ?></p>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="has-text-grey">Élève #<?= $grade['student_id'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="has-text-weight-semibold"><?= esc($grade['marks_obtained']) ?>/20</span>
                                </td>
                                <td>
                                    <?= number_format(($grade['marks_obtained'] / $exam['total_marks']) * 100, 1) ?>%
                                </td>
                                <td>
                                    <?php if ($grade['marks_obtained'] >= 10): ?>
                                        <span class="tag is-success">Réussi</span>
                                    <?php else: ?>
                                        <span class="tag is-danger">Échoué</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= esc($grade['remarks'] ?? '') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                <nav class="pagination is-centered" role="navigation" aria-label="pagination">
                    <ul class="pagination-list">
                        <?php if ($pagination['current_page'] > 1): ?>
                        <li>
                            <a href="<?= base_url('admin/examens/exams/' . $exam['id'] . '/view?page=' . ($pagination['current_page'] - 1) . '&limit=' . $pagination['per_page']) ?>" 
                               class="pagination-previous">
                                Précédent
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <li>
                            <a href="<?= base_url('admin/examens/exams/' . $exam['id'] . '/view?page=' . $i . '&limit=' . $pagination['per_page']) ?>" 
                               class="pagination-link <?= $i == $pagination['current_page'] ? 'is-current' : '' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <li>
                            <a href="<?= base_url('admin/examens/exams/' . $exam['id'] . '/view?page=' . ($pagination['current_page'] + 1) . '&limit=' . $pagination['per_page']) ?>" 
                               class="pagination-next">
                                Suivant
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                
                <!-- Informations de pagination -->
                <div class="has-text-centered mt-3">
                    <p class="has-text-grey">
                        Affichage de <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?> 
                        à <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?> 
                        sur <?= $pagination['total'] ?> notes
                    </p>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="box">
                <div class="has-text-centered">
                    <p class="has-text-grey">Aucune note saisie pour cet examen</p>
                    <a href="<?= base_url('admin/examens/grades/enter/' . $exam['id']) ?>" class="button is-success mt-3">
                        <span class="icon">
                            <i class="fas fa-edit"></i>
                        </span>
                        <span>Saisir les Notes</span>
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
