<?php
/**
 * Script de finalisation des vues des modules
 * Améliore les vues avec des données cohérentes et une logique métier
 */

echo "🚀 Finalisation des vues des modules KISSAI SCHOOL\n";
echo "===============================================\n\n";

// 1. Vue ÉCONOMAT
echo "1. Finalisation de la vue ÉCONOMAT...\n";
$economatView = <<<'PHP'
<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container">
    <h1 class="title">Module Économat</h1>
    
    <!-- Statistiques générales -->
    <div class="columns is-multiline">
        <div class="column is-3">
            <div class="box has-background-primary has-text-white">
                <h4 class="title is-4 has-text-white">Total Recettes</h4>
                <p class="title is-2 has-text-white"><?= number_format($total_revenue ?? 0) ?> FCFA</p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-success has-text-white">
                <h4 class="title is-4 has-text-white">Paiements Reçus</h4>
                <p class="title is-2 has-text-white"><?= $paid_payments ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-warning has-text-white">
                <h4 class="title is-4 has-text-white">En Attente</h4>
                <p class="title is-2 has-text-white"><?= $pending_payments ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-danger has-text-white">
                <h4 class="title is-4 has-text-white">Retards</h4>
                <p class="title is-2 has-text-white"><?= $overdue_payments ?? 0 ?></p>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="buttons mb-4">
        <a href="/admin/economat/payments" class="button is-primary">
            <span class="icon"><i class="fas fa-money-bill"></i></span>
            <span>Gestion des Paiements</span>
        </a>
        <a href="/admin/economat/fees" class="button is-info">
            <span class="icon"><i class="fas fa-list"></i></span>
            <span>Types de Frais</span>
        </a>
        <a href="/admin/economat/reports" class="button is-success">
            <span class="icon"><i class="fas fa-chart-bar"></i></span>
            <span>Rapports</span>
        </a>
    </div>

    <!-- Derniers paiements -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">Derniers Paiements</p>
        </header>
        <div class="card-content">
            <table class="table is-fullwidth">
                <thead>
                    <tr>
                        <th>Élève</th>
                        <th>Type de Frais</th>
                        <th>Montant</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($recent_payments) && !empty($recent_payments)): ?>
                        <?php foreach ($recent_payments as $payment): ?>
                        <tr>
                            <td><?= $payment->student_name ?></td>
                            <td><?= $payment->fee_type_name ?></td>
                            <td><?= number_format($payment->amount) ?> FCFA</td>
                            <td><?= date('d/m/Y', strtotime($payment->payment_date)) ?></td>
                            <td>
                                <span class="tag <?= $payment->status === 'paid' ? 'is-success' : 'is-warning' ?>">
                                    <?= $payment->status === 'paid' ? 'Payé' : 'En attente' ?>
                                </span>
                            </td>
                            <td>
                                <a href="/admin/economat/payments/<?= $payment->id ?>" class="button is-small is-info">
                                    <span class="icon"><i class="fas fa-eye"></i></span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="has-text-centered">Aucun paiement récent</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
PHP;

file_put_contents('app/Views/admin/economat/index.php', $economatView);
echo "✅ Vue ÉCONOMAT finalisée\n\n";

// 2. Vue SCOLARITÉ
echo "2. Finalisation de la vue SCOLARITÉ...\n";
$scolariteView = <<<'PHP'
<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container">
    <h1 class="title">Module Scolarité</h1>
    
    <!-- Statistiques générales -->
    <div class="columns is-multiline">
        <div class="column is-3">
            <div class="box has-background-info has-text-white">
                <h4 class="title is-4 has-text-white">Total Élèves</h4>
                <p class="title is-2 has-text-white"><?= $total_students ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-warning has-text-white">
                <h4 class="title is-4 has-text-white">Absences Aujourd'hui</h4>
                <p class="title is-2 has-text-white"><?= $today_absences ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-danger has-text-white">
                <h4 class="title is-4 has-text-white">Incidents Disciplinaires</h4>
                <p class="title is-2 has-text-white"><?= $discipline_incidents ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-success has-text-white">
                <h4 class="title is-4 has-text-white">Taux de Présence</h4>
                <p class="title is-2 has-text-white"><?= number_format($attendance_rate ?? 95, 1) ?>%</p>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="buttons mb-4">
        <a href="/admin/scolarite/students" class="button is-primary">
            <span class="icon"><i class="fas fa-users"></i></span>
            <span>Gestion des Élèves</span>
        </a>
        <a href="/admin/scolarite/absences" class="button is-warning">
            <span class="icon"><i class="fas fa-calendar-times"></i></span>
            <span>Absences</span>
        </a>
        <a href="/admin/scolarite/discipline" class="button is-danger">
            <span class="icon"><i class="fas fa-exclamation-triangle"></i></span>
            <span>Discipline</span>
        </a>
        <a href="/admin/scolarite/reports" class="button is-success">
            <span class="icon"><i class="fas fa-file-alt"></i></span>
            <span>Rapports</span>
        </a>
    </div>

    <!-- Derniers élèves inscrits -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">Derniers Élèves Inscrits</p>
        </header>
        <div class="card-content">
            <table class="table is-fullwidth">
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Nom</th>
                        <th>Classe</th>
                        <th>Date d'inscription</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($recent_students) && !empty($recent_students)): ?>
                        <?php foreach ($recent_students as $student): ?>
                        <tr>
                            <td><?= $student->matricule ?></td>
                            <td><?= $student->first_name . ' ' . $student->last_name ?></td>
                            <td><?= $student->class_name ?></td>
                            <td><?= date('d/m/Y', strtotime($student->enrollment_date)) ?></td>
                            <td>
                                <span class="tag <?= $student->is_active ? 'is-success' : 'is-danger' ?>">
                                    <?= $student->is_active ? 'Actif' : 'Inactif' ?>
                                </span>
                            </td>
                            <td>
                                <a href="/admin/scolarite/students/<?= $student->id ?>" class="button is-small is-info">
                                    <span class="icon"><i class="fas fa-eye"></i></span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="has-text-centered">Aucun élève récent</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
PHP;

file_put_contents('app/Views/admin/scolarite/index.php', $scolariteView);
echo "✅ Vue SCOLARITÉ finalisée\n\n";

// 3. Vue ÉTUDES
echo "3. Finalisation de la vue ÉTUDES...\n";
$etudesView = <<<'PHP'
<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container">
    <h1 class="title">Module Études</h1>
    
    <!-- Statistiques générales -->
    <div class="columns is-multiline">
        <div class="column is-3">
            <div class="box has-background-primary has-text-white">
                <h4 class="title is-4 has-text-white">Total Classes</h4>
                <p class="title is-2 has-text-white"><?= $total_classes ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-info has-text-white">
                <h4 class="title is-4 has-text-white">Total Matières</h4>
                <p class="title is-2 has-text-white"><?= $total_subjects ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-success has-text-white">
                <h4 class="title is-4 has-text-white">Total Enseignants</h4>
                <p class="title is-2 has-text-white"><?= $total_teachers ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-warning has-text-white">
                <h4 class="title is-4 has-text-white">Effectif Total</h4>
                <p class="title is-2 has-text-white"><?= $total_students ?? 0 ?></p>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="buttons mb-4">
        <a href="/admin/etudes/classes" class="button is-primary">
            <span class="icon"><i class="fas fa-chalkboard"></i></span>
            <span>Gestion des Classes</span>
        </a>
        <a href="/admin/etudes/subjects" class="button is-info">
            <span class="icon"><i class="fas fa-book"></i></span>
            <span>Matières</span>
        </a>
        <a href="/admin/etudes/timetables" class="button is-success">
            <span class="icon"><i class="fas fa-calendar-alt"></i></span>
            <span>Emplois du Temps</span>
        </a>
        <a href="/admin/etudes/assignments" class="button is-warning">
            <span class="icon"><i class="fas fa-user-tie"></i></span>
            <span>Assignations</span>
        </a>
    </div>

    <!-- Classes par niveau -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">Classes par Niveau</p>
        </header>
        <div class="card-content">
            <table class="table is-fullwidth">
                <thead>
                    <tr>
                        <th>Niveau</th>
                        <th>Classes</th>
                        <th>Effectif</th>
                        <th>Enseignant Principal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($classes_by_level) && !empty($classes_by_level)): ?>
                        <?php foreach ($classes_by_level as $level): ?>
                        <tr>
                            <td><?= $level->level_name ?></td>
                            <td><?= $level->class_count ?></td>
                            <td><?= $level->total_students ?></td>
                            <td><?= $level->teacher_name ?? 'Non assigné' ?></td>
                            <td>
                                <a href="/admin/etudes/classes/level/<?= $level->level_id ?>" class="button is-small is-info">
                                    <span class="icon"><i class="fas fa-eye"></i></span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="has-text-centered">Aucune classe trouvée</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
PHP;

file_put_contents('app/Views/admin/etudes/index.php', $etudesView);
echo "✅ Vue ÉTUDES finalisée\n\n";

echo "🎉 Finalisation des vues principales terminée !\n";
echo "Les vues sont maintenant cohérentes avec la logique métier.\n";
?>


