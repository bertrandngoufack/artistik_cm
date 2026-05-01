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
                    <li class="is-active"><a href="#" aria-current="page">Bulletins de Notes</a></li>
                </ul>
            </nav>

            <!-- Header -->
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <h1 class="title">Bulletins de Notes</h1>
                    </div>
                </div>
            </div>

            <!-- Formulaire de génération -->
            <div class="box">
                <h3 class="title is-5">
                    <span class="icon">
                        <i class="fas fa-file-alt"></i>
                    </span>
                    Génération des Bulletins
                </h3>
                
                <form action="<?= base_url('admin/examens/report-cards/generate') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="columns">
                        <div class="column is-6">
                            <div class="field">
                                <label class="label" for="class_id">Classe *</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select id="class_id" name="class_id" required>
                                            <option value="">Sélectionner une classe</option>
                                            <?php if (isset($classes)): ?>
                                                <?php foreach ($classes as $class): ?>
                                                    <option value="<?= $class['id'] ?>" <?= old('class_id') == $class['id'] ? 'selected' : '' ?>>
                                                        <?= $class['name'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label" for="exam_id">Examen (Optionnel)</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select id="exam_id" name="exam_id">
                                            <option value="">Tous les examens</option>
                                            <?php if (isset($exams)): ?>
                                                <?php foreach ($exams as $exam): ?>
                                                    <option value="<?= $exam['id'] ?>" <?= old('exam_id') == $exam['id'] ? 'selected' : '' ?>>
                                                        <?= $exam['name'] ?> - <?= date('d/m/Y', strtotime($exam['exam_date'])) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column is-6">
                            <div class="field">
                                <label class="label" for="period">Période</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select id="period" name="period">
                                            <option value="">Toutes les périodes</option>
                                            <option value="1" <?= old('period') == '1' ? 'selected' : '' ?>>1er Trimestre</option>
                                            <option value="2" <?= old('period') == '2' ? 'selected' : '' ?>>2ème Trimestre</option>
                                            <option value="3" <?= old('period') == '3' ? 'selected' : '' ?>>3ème Trimestre</option>
                                            <option value="annual" <?= old('period') == 'annual' ? 'selected' : '' ?>>Annuel</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label" for="format">Format d'Export</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select id="format" name="format">
                                            <option value="pdf" <?= old('format', 'pdf') == 'pdf' ? 'selected' : '' ?>>PDF</option>
                                            <option value="excel" <?= old('format') == 'excel' ? 'selected' : '' ?>>Excel</option>
                                            <option value="csv" <?= old('format') == 'csv' ? 'selected' : '' ?>>CSV</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="checkbox">
                            <input type="checkbox" name="include_rankings" value="1" <?= old('include_rankings') ? 'checked' : '' ?>>
                            Inclure les classements
                        </label>
                    </div>

                    <div class="field">
                        <label class="checkbox">
                            <input type="checkbox" name="include_comments" value="1" <?= old('include_comments') ? 'checked' : '' ?>>
                            Inclure les commentaires
                        </label>
                    </div>

                    <div class="field is-grouped">
                        <div class="control">
                            <button type="submit" class="button is-primary">
                                <span class="icon">
                                    <i class="fas fa-file-pdf"></i>
                                </span>
                                <span>Générer les Bulletins</span>
                            </button>
                        </div>
                        <div class="control">
                            <a href="<?= base_url('admin/examens') ?>" class="button is-light">
                                <span class="icon">
                                    <i class="fas fa-times"></i>
                                </span>
                                <span>Annuler</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Historique des bulletins générés -->
            <div class="box">
                <h3 class="title is-5">
                    <span class="icon">
                        <i class="fas fa-history"></i>
                    </span>
                    Historique des Bulletins Générés
                </h3>
                
                <div class="table-container">
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Date de Génération</th>
                                <th>Classe</th>
                                <th>Période</th>
                                <th>Format</th>
                                <th>Nombre d'Élèves</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($generatedReports) && !empty($generatedReports)): ?>
                                <?php foreach ($generatedReports as $report): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($report['generated_at'])) ?></td>
                                    <td><?= esc($report['class_name']) ?></td>
                                    <td><?= esc($report['period']) ?></td>
                                    <td>
                                        <span class="tag is-info"><?= strtoupper($report['format']) ?></span>
                                    </td>
                                    <td><?= esc($report['student_count']) ?></td>
                                    <td>
                                        <div class="buttons are-small">
                                            <a href="<?= base_url('admin/examens/report-cards/download/' . $report['id']) ?>" class="button is-success">
                                                <span class="icon">
                                                    <i class="fas fa-download"></i>
                                                </span>
                                                <span>Télécharger</span>
                                            </a>
                                            <a href="<?= base_url('admin/examens/report-cards/delete/' . $report['id']) ?>" 
                                               class="button is-danger"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce bulletin ?')">
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
                                    <td colspan="6" class="has-text-centered">
                                        <p class="has-text-grey">Aucun bulletin généré</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>



















