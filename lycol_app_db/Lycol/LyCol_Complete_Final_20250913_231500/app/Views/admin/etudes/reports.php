<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <!-- En-tête -->
    <div class="level mb-5">
        <div class="level-left">
            <div class="level-item">
                <h1 class="title is-2">Rapports Études</h1>
                <p class="subtitle is-5 has-text-grey">Génération et export des rapports académiques</p>
            </div>
        </div>
        <div class="level-right">
            <div class="level-item">
                <a href="<?= base_url('admin/etudes') ?>" class="button is-light">
                    <span class="icon"><i class="fas fa-arrow-left"></i></span>
                    <span>Retour</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="columns is-multiline mb-6">
        <div class="column is-3">
            <div class="box has-background-primary has-text-white">
                <div class="has-text-centered">
                    <span class="icon is-large has-text-white mb-3">
                        <i class="fas fa-chalkboard fa-2x"></i>
                    </span>
                    <h4 class="title is-5 has-text-white mb-2">Total Classes</h4>
                    <p class="title is-2 has-text-white"><?= $stats['totalClasses'] ?? 0 ?></p>
                </div>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-success has-text-white">
                <div class="has-text-centered">
                    <span class="icon is-large has-text-white mb-3">
                        <i class="fas fa-book fa-2x"></i>
                    </span>
                    <h4 class="title is-5 has-text-white mb-2">Total Matières</h4>
                    <p class="title is-2 has-text-white"><?= $stats['totalSubjects'] ?? 0 ?></p>
                </div>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-info has-text-white">
                <div class="has-text-centered">
                    <span class="icon is-large has-text-white mb-3">
                        <i class="fas fa-layer-group fa-2x"></i>
                    </span>
                    <h4 class="title is-5 has-text-white mb-2">Total Cycles</h4>
                    <p class="title is-2 has-text-white"><?= $stats['totalCycles'] ?? 0 ?></p>
                </div>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-warning has-text-white">
                <div class="has-text-centered">
                    <span class="icon is-large has-text-white mb-3">
                        <i class="fas fa-chalkboard-teacher fa-2x"></i>
                    </span>
                    <h4 class="title is-5 has-text-white mb-2">Total Enseignants</h4>
                    <p class="title is-2 has-text-white"><?= $stats['totalTeachers'] ?? 0 ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire de génération de rapport -->
    <div class="columns">
        <div class="column is-8">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon mr-2">
                            <i class="fas fa-file-alt"></i>
                        </span>
                        Générer un Rapport
                    </p>
                </header>
                <div class="card-content">
                    <form action="<?= base_url('admin/etudes/reports/generate') ?>" method="POST">
                        <!-- Type de rapport -->
                        <div class="field">
                            <label class="label">Type de rapport</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="report_type" required>
                                        <option value="">Sélectionner un type de rapport</option>
                                        <option value="summary">Rapport général</option>
                                        <option value="cycles">Rapport par cycle</option>
                                        <option value="classes">Rapport par classe</option>
                                        <option value="subjects">Rapport par matière</option>
                                        <option value="assignments">Rapport des assignations</option>
                                        <option value="timetable">Rapport des emplois du temps</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Filtres -->
                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Cycle</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="cycle_id">
                                                <option value="">Tous les cycles</option>
                                                <?php foreach ($cycles as $cycle): ?>
                                                    <option value="<?= $cycle['id'] ?>"><?= esc($cycle['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Classe</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="class_id">
                                                <option value="">Toutes les classes</option>
                                                <?php foreach ($classes as $class): ?>
                                                    <option value="<?= $class['id'] ?>"><?= esc($class['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Matière</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="subject_id">
                                                <option value="">Toutes les matières</option>
                                                <?php foreach ($subjects as $subject): ?>
                                                    <option value="<?= $subject['id'] ?>"><?= esc($subject['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Enseignant</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="teacher_id">
                                                <option value="">Tous les enseignants</option>
                                                <?php foreach ($teachers as $teacher): ?>
                                                    <option value="<?= $teacher['id'] ?>"><?= esc($teacher['first_name'] . ' ' . $teacher['last_name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Année académique</label>
                                    <div class="control">
                                        <input class="input" type="text" name="academic_year" value="<?= $current_academic_year ?? '2024-2025' ?>" placeholder="2024-2025">
                                    </div>
                                </div>
                            </div>
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Format d'export</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="format">
                                                <option value="html">HTML (Aperçu)</option>
                                                <option value="pdf">PDF</option>
                                                <option value="csv">CSV</option>
                                                <option value="excel">Excel</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="field is-grouped">
                            <div class="control">
                                <button type="submit" class="button is-primary">
                                    <span class="icon"><i class="fas fa-file-alt"></i></span>
                                    <span>Générer le rapport</span>
                                </button>
                            </div>
                            <div class="control">
                                <button type="reset" class="button is-light">
                                    <span class="icon"><i class="fas fa-undo"></i></span>
                                    <span>Réinitialiser</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Rapports rapides -->
        <div class="column is-4">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon mr-2">
                            <i class="fas fa-bolt"></i>
                        </span>
                        Rapports Rapides
                    </p>
                </header>
                <div class="card-content">
                    <div class="buttons is-vertical is-fullwidth">
                        <a href="<?= base_url('admin/etudes/reports/generate?report_type=summary&format=html') ?>" class="button is-info is-fullwidth">
                            <span class="icon"><i class="fas fa-chart-pie"></i></span>
                            <span>Rapport général</span>
                        </a>
                        <a href="<?= base_url('admin/etudes/reports/generate?report_type=cycles&format=html') ?>" class="button is-success is-fullwidth">
                            <span class="icon"><i class="fas fa-layer-group"></i></span>
                            <span>Rapport par cycle</span>
                        </a>
                        <a href="<?= base_url('admin/etudes/reports/generate?report_type=classes&format=html') ?>" class="button is-warning is-fullwidth">
                            <span class="icon"><i class="fas fa-chalkboard"></i></span>
                            <span>Rapport par classe</span>
                        </a>
                        <a href="<?= base_url('admin/etudes/reports/generate?report_type=assignments&format=html') ?>" class="button is-danger is-fullwidth">
                            <span class="icon"><i class="fas fa-user-tie"></i></span>
                            <span>Rapport des assignations</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Export rapide -->
            <div class="card mt-4">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon mr-2">
                            <i class="fas fa-download"></i>
                        </span>
                        Export Rapide
                    </p>
                </header>
                <div class="card-content">
                    <div class="buttons is-vertical is-fullwidth">
                        <a href="<?= base_url('admin/etudes/reports/export/csv?report_type=summary') ?>" class="button is-small is-outlined is-fullwidth">
                            <span class="icon"><i class="fas fa-file-csv"></i></span>
                            <span>Export CSV - Général</span>
                        </a>
                        <a href="<?= base_url('admin/etudes/reports/export/csv?report_type=assignments') ?>" class="button is-small is-outlined is-fullwidth">
                            <span class="icon"><i class="fas fa-file-csv"></i></span>
                            <span>Export CSV - Assignations</span>
                        </a>
                        <a href="<?= base_url('admin/etudes/reports/export/csv?report_type=classes') ?>" class="button is-small is-outlined is-fullwidth">
                            <span class="icon"><i class="fas fa-file-csv"></i></span>
                            <span>Export CSV - Classes</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

.buttons.is-vertical .button {
    margin-bottom: 0.5rem;
}

.buttons.is-vertical .button:last-child {
    margin-bottom: 0;
}
</style>

<?= $this->endSection() ?>


















