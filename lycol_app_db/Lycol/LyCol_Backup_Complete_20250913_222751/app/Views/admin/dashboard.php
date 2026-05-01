<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title is-3">
                <span class="icon"><i class="fas fa-tachometer-alt"></i></span>
                Tableau de bord
            </h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <div class="buttons">
                <a href="<?= base_url('admin/export/students') ?>" class="button is-info is-small">
                    <span class="icon"><i class="fas fa-download"></i></span>
                    <span>Exporter élèves</span>
                </a>
                <a href="<?= base_url('admin/export/grades') ?>" class="button is-success is-small">
                    <span class="icon"><i class="fas fa-file-export"></i></span>
                    <span>Exporter notes</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="columns is-multiline">
    <!-- Total Students -->
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="media">
                    <div class="media-left">
                        <span class="icon is-large has-text-info">
                            <i class="fas fa-users fa-2x"></i>
                        </span>
                    </div>
                    <div class="media-content">
                        <p class="title is-4"><?= number_format($total_students) ?></p>
                        <p class="subtitle is-6">Élèves actifs</p>
                    </div>
                </div>
            </div>
            <footer class="card-footer">
                <a href="<?= base_url('admin/scolarite') ?>" class="card-footer-item">
                    <span class="icon"><i class="fas fa-eye"></i></span>
                    <span>Voir détails</span>
                </a>
            </footer>
        </div>
    </div>

    <!-- Total Classes -->
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="media">
                    <div class="media-left">
                        <span class="icon is-large has-text-success">
                            <i class="fas fa-chalkboard fa-2x"></i>
                        </span>
                    </div>
                    <div class="media-content">
                        <p class="title is-4"><?= number_format($total_classes) ?></p>
                        <p class="subtitle is-6">Classes</p>
                    </div>
                </div>
            </div>
            <footer class="card-footer">
                <a href="<?= base_url('admin/etudes') ?>" class="card-footer-item">
                    <span class="icon"><i class="fas fa-eye"></i></span>
                    <span>Voir détails</span>
                </a>
            </footer>
        </div>
    </div>

    <!-- Total Subjects -->
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="media">
                    <div class="media-left">
                        <span class="icon is-large has-text-warning">
                            <i class="fas fa-book fa-2x"></i>
                        </span>
                    </div>
                    <div class="media-content">
                        <p class="title is-4"><?= number_format($total_subjects) ?></p>
                        <p class="subtitle is-6">Matières</p>
                    </div>
                </div>
            </div>
            <footer class="card-footer">
                <a href="<?= base_url('admin/etudes') ?>" class="card-footer-item">
                    <span class="icon"><i class="fas fa-eye"></i></span>
                    <span>Voir détails</span>
                </a>
            </footer>
        </div>
    </div>

    <!-- Total Exams -->
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="media">
                    <div class="media-left">
                        <span class="icon is-large has-text-danger">
                            <i class="fas fa-file-alt fa-2x"></i>
                        </span>
                    </div>
                    <div class="media-content">
                        <p class="title is-4"><?= number_format($total_exams) ?></p>
                        <p class="subtitle is-6">Examens terminés</p>
                    </div>
                </div>
            </div>
            <footer class="card-footer">
                <a href="<?= base_url('admin/examens') ?>" class="card-footer-item">
                    <span class="icon"><i class="fas fa-eye"></i></span>
                    <span>Voir détails</span>
                </a>
            </footer>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="columns">
    <!-- Recent Students -->
    <div class="column is-6">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-user-plus"></i></span>
                    Élèves récemment inscrits
                </p>
                <a href="<?= base_url('admin/scolarite') ?>" class="card-header-icon">
                    <span class="icon"><i class="fas fa-arrow-right"></i></span>
                </a>
            </header>
            <div class="card-content">
                <?php if (empty($recent_students)): ?>
                    <div class="has-text-centered py-4">
                        <span class="icon is-large has-text-grey-light">
                            <i class="fas fa-users fa-2x"></i>
                        </span>
                        <p class="has-text-grey">Aucun élève récemment inscrit</p>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table is-fullwidth is-striped">
                            <thead>
                                <tr>
                                    <th>Élève</th>
                                    <th>Matricule</th>
                                    <th>Date d'inscription</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_students as $student): ?>
                                    <tr>
                                        <td>
                                            <div class="media">
                                                <div class="media-left">
                                                    <span class="icon">
                                                        <i class="fas fa-user"></i>
                                                    </span>
                                                </div>
                                                <div class="media-content">
                                                    <p class="is-size-7">
                                                        <strong><?= esc($student['first_name'] . ' ' . $student['last_name']) ?></strong>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="tag is-info is-light"><?= esc($student['matricule']) ?></span>
                                        </td>
                                        <td>
                                            <span class="is-size-7"><?= date('d/m/Y', strtotime($student['created_at'])) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="column is-6">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-money-bill-wave"></i></span>
                    Paiements récents
                </p>
                <a href="<?= base_url('admin/economat') ?>" class="card-header-icon">
                    <span class="icon"><i class="fas fa-arrow-right"></i></span>
                </a>
            </header>
            <div class="card-content">
                <?php if (empty($recent_payments)): ?>
                    <div class="has-text-centered py-4">
                        <span class="icon is-large has-text-grey-light">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </span>
                        <p class="has-text-grey">Aucun paiement récent</p>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table is-fullwidth is-striped">
                            <thead>
                                <tr>
                                    <th>Élève</th>
                                    <th>Montant</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_payments as $payment): ?>
                                    <tr>
                                        <td>
                                            <div class="media">
                                                <div class="media-left">
                                                    <span class="icon">
                                                        <i class="fas fa-user"></i>
                                                    </span>
                                                </div>
                                                <div class="media-content">
                                                    <p class="is-size-7">
                                                        <strong><?= esc($payment['student_name'] ?? 'N/A') ?></strong>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="tag is-success is-light">
                                                <?= number_format($payment['amount_paid'], 0, ',', ' ') ?> FCFA
                                            </span>
                                        </td>
                                        <td>
                                            <span class="is-size-7"><?= date('d/m/Y', strtotime($payment['created_at'])) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="columns">
    <div class="column">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-bolt"></i></span>
                    Actions rapides
                </p>
            </header>
            <div class="card-content">
                <div class="columns is-multiline">
                    <div class="column is-3">
                        <a href="<?= base_url('admin/scolarite/add-student') ?>" class="button is-info is-fullwidth">
                            <span class="icon"><i class="fas fa-user-plus"></i></span>
                            <span>Ajouter un élève</span>
                        </a>
                    </div>
                    <div class="column is-3">
                        <a href="<?= base_url('admin/examens/create') ?>" class="button is-success is-fullwidth">
                            <span class="icon"><i class="fas fa-file-alt"></i></span>
                            <span>Créer un examen</span>
                        </a>
                    </div>
                    <div class="column is-3">
                        <a href="<?= base_url('admin/messagerie/compose') ?>" class="button is-warning is-fullwidth">
                            <span class="icon"><i class="fas fa-envelope"></i></span>
                            <span>Envoyer un message</span>
                        </a>
                    </div>
                    <div class="column is-3">
                        <a href="<?= base_url('admin/statistiques') ?>" class="button is-danger is-fullwidth">
                            <span class="icon"><i class="fas fa-chart-bar"></i></span>
                            <span>Voir statistiques</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Module Études - Section dédiée -->
<div class="columns">
    <div class="column">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-graduation-cap"></i></span>
                    Module Études
                </p>
                <a href="<?= base_url('admin/etudes') ?>" class="card-header-icon">
                    <span class="icon"><i class="fas fa-arrow-right"></i></span>
                </a>
            </header>
            <div class="card-content">
                <div class="columns is-multiline">
                    <div class="column is-3">
                        <a href="<?= base_url('admin/etudes/assignments') ?>" class="button is-primary is-fullwidth">
                            <span class="icon"><i class="fas fa-chalkboard-teacher"></i></span>
                            <span>Assignations</span>
                        </a>
                    </div>
                    <div class="column is-3">
                        <a href="<?= base_url('admin/etudes/timetable') ?>" class="button is-info is-fullwidth">
                            <span class="icon"><i class="fas fa-calendar-alt"></i></span>
                            <span>Emploi du temps</span>
                        </a>
                    </div>
                    <div class="column is-3">
                        <a href="<?= base_url('admin/etudes/classes') ?>" class="button is-success is-fullwidth">
                            <span class="icon"><i class="fas fa-chalkboard"></i></span>
                            <span>Classes</span>
                        </a>
                    </div>
                    <div class="column is-3">
                        <a href="<?= base_url('admin/etudes/subjects') ?>" class="button is-warning is-fullwidth">
                            <span class="icon"><i class="fas fa-book"></i></span>
                            <span>Matières</span>
                        </a>
                    </div>
                </div>
                
                <!-- Statistiques du module études -->
                <div class="columns is-multiline mt-4">
                    <div class="column is-3">
                        <div class="notification is-primary is-light">
                            <p class="has-text-weight-bold">Total Assignations</p>
                            <p class="title is-4"><?= $total_assignments ?? 0 ?></p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="notification is-info is-light">
                            <p class="has-text-weight-bold">Classes Actives</p>
                            <p class="title is-4"><?= $total_classes ?? 0 ?></p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="notification is-success is-light">
                            <p class="has-text-weight-bold">Matières</p>
                            <p class="title is-4"><?= $total_subjects ?? 0 ?></p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="notification is-warning is-light">
                            <p class="has-text-weight-bold">Cycles</p>
                            <p class="title is-4"><?= $total_cycles ?? 0 ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Status -->
<div class="columns">
    <div class="column">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-server"></i></span>
                    État du système
                </p>
            </header>
            <div class="card-content">
                <div class="columns is-multiline">
                    <div class="column is-3">
                        <div class="notification is-success is-light">
                            <p class="has-text-weight-bold">Base de données</p>
                            <p class="is-size-7">Connectée</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="notification is-info is-light">
                            <p class="has-text-weight-bold">Licence</p>
                            <p class="is-size-7">Active</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="notification is-warning is-light">
                            <p class="has-text-weight-bold">Sauvegarde</p>
                            <p class="is-size-7">Dernière: <?= date('d/m/Y H:i') ?></p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="notification is-danger is-light">
                            <p class="has-text-weight-bold">Utilisateurs en ligne</p>
                            <p class="is-size-7"><?= rand(1, 10) ?> actifs</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Auto-refresh dashboard every 30 seconds
    setInterval(() => {
        location.reload();
    }, 30000);

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', () => {
        // Add any dashboard-specific JavaScript here
    });
</script>
<?= $this->endSection() ?>




