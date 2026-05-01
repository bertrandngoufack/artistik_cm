<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <!-- En-tête -->
    <div class="level mb-5">
        <div class="level-left">
            <div class="level-item">
                <h1 class="title is-2">Module Études</h1>
                <p class="subtitle is-5 has-text-grey">Gestion académique et pédagogique</p>
            </div>
        </div>
        <div class="level-right">
            <div class="level-item">
                <a href="<?= base_url('admin/etudes/reports') ?>" class="button is-primary">
                    <span class="icon"><i class="fas fa-download"></i></span>
                    <span>Générer Rapport</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
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

    <!-- Contenu principal -->
    <div class="columns">
        <!-- Menu de navigation -->
        <div class="column is-8">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon mr-2">
                            <i class="fas fa-cogs"></i>
                        </span>
                        Gestion des Études
                    </p>
                </header>
                <div class="card-content">
                    <div class="columns is-multiline">
                        <!-- Cycles -->
                        <div class="column is-6">
                            <div class="card has-background-primary has-text-white">
                                <div class="card-content p-4">
                                    <div class="media">
                                        <div class="media-left">
                                            <span class="icon is-large has-text-white">
                                                <i class="fas fa-layer-group fa-2x"></i>
                                            </span>
                                        </div>
                                        <div class="media-content">
                                            <h4 class="title is-5 has-text-white mb-2">Cycles d'Études</h4>
                                            <p class="subtitle is-6 has-text-white mb-3">Primaire, Collège, Lycée</p>
                                            <a href="<?= base_url('admin/etudes/cycles') ?>" class="button is-white is-outlined is-small">
                                                <span class="icon"><i class="fas fa-arrow-right"></i></span>
                                                <span>Gérer les Cycles</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Classes -->
                        <div class="column is-6">
                            <div class="card has-background-success has-text-white">
                                <div class="card-content p-4">
                                    <div class="media">
                                        <div class="media-left">
                                            <span class="icon is-large has-text-white">
                                                <i class="fas fa-chalkboard fa-2x"></i>
                                            </span>
                                        </div>
                                        <div class="media-content">
                                            <h4 class="title is-5 has-text-white mb-2">Classes</h4>
                                            <p class="subtitle is-6 has-text-white mb-3">des classes et niveaux</p>
                                            <a href="<?= base_url('admin/etudes/classes') ?>" class="button is-white is-outlined is-small">
                                                <span class="icon"><i class="fas fa-arrow-right"></i></span>
                                                <span>Gérer les Classes</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Matières -->
                        <div class="column is-6">
                            <div class="card has-background-info has-text-white">
                                <div class="card-content p-4">
                                    <div class="media">
                                        <div class="media-left">
                                            <span class="icon is-large has-text-white">
                                                <i class="fas fa-book fa-2x"></i>
                                            </span>
                                        </div>
                                        <div class="media-content">
                                            <h4 class="title is-5 has-text-white mb-2">Matières</h4>
                                            <p class="subtitle is-6 has-text-white mb-3">matières et programmes</p>
                                            <a href="<?= base_url('admin/etudes/subjects') ?>" class="button is-white is-outlined is-small">
                                                <span class="icon"><i class="fas fa-arrow-right"></i></span>
                                                <span>Gérer les Matières</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Emploi du Temps -->
                        <div class="column is-6">
                            <div class="card has-background-warning has-text-white">
                                <div class="card-content p-4">
                                    <div class="media">
                                        <div class="media-left">
                                            <span class="icon is-large has-text-white">
                                                <i class="fas fa-clock fa-2x"></i>
                                            </span>
                                        </div>
                                        <div class="media-content">
                                            <h4 class="title is-5 has-text-white mb-2">Emploi du Temps</h4>
                                            <p class="subtitle is-6 has-text-white mb-3">planification des cours</p>
                                            <a href="<?= base_url('admin/etudes/timetable') ?>" class="button is-white is-outlined is-small">
                                                <span class="icon"><i class="fas fa-arrow-right"></i></span>
                                                <span>Gérer l'Emploi du Temps</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assignations -->
                        <div class="column is-6">
                            <div class="card has-background-danger has-text-white">
                                <div class="card-content p-4">
                                    <div class="media">
                                        <div class="media-left">
                                            <span class="icon is-large has-text-white">
                                                <i class="fas fa-user-tie fa-2x"></i>
                                            </span>
                                        </div>
                                        <div class="media-content">
                                            <h4 class="title is-5 has-text-white mb-2">Assignations</h4>
                                            <p class="subtitle is-6 has-text-white mb-3">enseignants</p>
                                            <a href="<?= base_url('admin/etudes/assignments') ?>" class="button is-white is-outlined is-small">
                                                <span class="icon"><i class="fas fa-arrow-right"></i></span>
                                                <span>Gérer les Assignations</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques des cycles -->
        <div class="column is-4">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon mr-2">
                            <i class="fas fa-chart-pie"></i>
                        </span>
                        Statistiques par Cycle
                    </p>
                </header>
                <div class="card-content">
                    <?php if (!empty($stats['cyclesStats'])): ?>
                        <?php foreach ($stats['cyclesStats'] as $cycle): ?>
                            <div class="mb-4">
                                <div class="level mb-2">
                                    <div class="level-left">
                                        <div class="level-item">
                                            <strong><?= esc($cycle['name']) ?></strong>
                                        </div>
                                    </div>
                                    <div class="level-right">
                                        <div class="level-item">
                                            <span class="tag is-primary"><?= $cycle['class_count'] ?? 0 ?> classes</span>
                                        </div>
                                    </div>
                                </div>
                                <progress class="progress is-primary mt-2" 
                                          value="<?= $cycle['class_count'] ?? 0 ?>" 
                                          max="<?= $stats['totalClasses'] ?>">
                                    <?= $cycle['class_count'] ?? 0 ?>%
                                </progress>
                                <p class="help">Capacité totale: <?= $cycle['total_capacity'] ?? 0 ?> élèves</p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="has-text-centered py-4">
                            <span class="icon is-large has-text-grey-light">
                                <i class="fas fa-info-circle"></i>
                            </span>
                            <p class="has-text-grey-light mt-2">Aucune donnée disponible</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Assignations récentes -->
            <div class="card mt-4">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon mr-2">
                            <i class="fas fa-clock"></i>
                        </span>
                        Assignations Récentes
                    </p>
                </header>
                <div class="card-content">
                    <?php if (!empty($recent_assignments)): ?>
                        <?php foreach ($recent_assignments as $assignment): ?>
                            <div class="media mb-3">
                                <div class="media-left">
                                    <span class="icon has-text-info">
                                        <i class="fas fa-user-tie"></i>
                                    </span>
                                </div>
                                <div class="media-content">
                                    <p class="is-size-7">
                                        <strong><?= esc(($assignment['first_name'] ?? '') . ' ' . ($assignment['last_name'] ?? '')) ?></strong>
                                        <br>
                                        <span class="has-text-grey"><?= esc($assignment['subject_name'] ?? 'N/A') ?> - <?= esc($assignment['class_name'] ?? 'N/A') ?></span>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="has-text-centered py-3">
                            <p class="has-text-grey-light is-size-7">Aucune assignation récente</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS spécifique pour corriger les superpositions -->
<link rel="stylesheet" href="<?= base_url('assets/css/etudes-fixes.css') ?>">

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

.media-content .title,
.media-content .subtitle {
    word-break: break-word;
    line-height: 1.3;
}

.icon.is-large {
    width: 3rem;
    height: 3rem;
}
</style>

<?= $this->endSection() ?>
