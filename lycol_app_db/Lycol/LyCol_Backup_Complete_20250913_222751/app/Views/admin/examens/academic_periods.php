<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="level">
        <div class="level-left">
            <div class="level-item">
                <h1 class="title">Gestion des Périodes Académiques</h1>
            </div>
        </div>
        <div class="level-right">
            <div class="level-item">
                <a href="<?= base_url('admin/examens') ?>" class="button is-info">
                    <span class="icon"><i class="fas fa-arrow-left"></i></span>
                    <span>Retour</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Sélecteur d'année académique -->
    <div class="notification is-info is-light">
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <strong>Année Académique :</strong> 
                    <span class="tag is-info is-medium"><?= $academicYear ?? $current_academic_year ?? '2024-2025' ?></span>
                </div>
            </div>
            <div class="level-right">
                <div class="level-item">
                    <form action="<?= base_url('admin/examens/academic-periods') ?>" method="get" class="field has-addons">
                        <div class="control">
                            <div class="select">
                                <select name="academic_year" onchange="this.form.submit()">
                                    <?php foreach ($availableYears as $year): ?>
                                    <option value="<?= $year['academic_year'] ?>" <?= ($year['academic_year'] == ($academicYear ?? $current_academic_year ?? '2024-2025')) ? 'selected' : '' ?>>
                                        <?= $year['academic_year'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="control">
                            <button type="submit" class="button is-info">
                                <span class="icon"><i class="fas fa-search"></i></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Période actuelle -->
    <?php if ($currentPeriod): ?>
    <div class="notification is-success is-light">
        <strong>Période Académique Actuelle :</strong> 
        <span class="tag is-success is-medium"><?= $currentPeriod['name'] ?></span>
        <span class="tag is-info is-medium"><?= date('d/m/Y', strtotime($currentPeriod['start_date'])) ?> - <?= date('d/m/Y', strtotime($currentPeriod['end_date'])) ?></span>
    </div>
    <?php else: ?>
    <div class="notification is-warning is-light">
                        <strong>Aucune période active actuellement pour l'année <?= $academicYear ?? $current_academic_year ?? '2024-2025' ?></strong>
    </div>
    <?php endif; ?>

    <!-- Création d'une nouvelle année académique -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="fas fa-plus-circle"></i></span>
                Créer une Nouvelle Année Académique
            </p>
        </header>
        <div class="card-content">
            <form action="<?= base_url('admin/examens/academic-periods/create-year') ?>" method="post">
                <div class="field has-addons">
                    <div class="control">
                        <input class="input" type="text" name="academic_year" placeholder="2025-2026" pattern="\d{4}-\d{4}" required>
                    </div>
                    <div class="control">
                        <button type="submit" class="button is-success">
                            <span class="icon"><i class="fas fa-plus"></i></span>
                            <span>Créer Année</span>
                        </button>
                    </div>
                </div>
                <p class="help">Format: AAAA-AAAA (ex: 2025-2026)</p>
            </form>
        </div>
    </div>

    <!-- Configuration des périodes -->
    <div class="columns is-multiline">
        <?php if (!empty($periods)): ?>
            <?php foreach ($periods as $period): ?>
            <div class="column is-4">
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            <span class="icon"><i class="fas fa-calendar-alt"></i></span>
                            <?= $period['name'] ?>
                        </p>
                    </header>
                    <div class="card-content">
                        <form action="<?= base_url('admin/examens/academic-periods/update') ?>" method="post">
                            <input type="hidden" name="period_id" value="<?= $period['id'] ?>">
                            
                            <div class="field">
                                <label class="label">Date de début</label>
                                <div class="control">
                                    <input class="input" type="date" name="start_date" value="<?= $period['start_date'] ?>" required>
                                </div>
                            </div>
                            
                            <div class="field">
                                <label class="label">Date de fin</label>
                                <div class="control">
                                    <input class="input" type="date" name="end_date" value="<?= $period['end_date'] ?>" required>
                                </div>
                            </div>
                            
                            <div class="field">
                                <div class="control">
                                    <button type="submit" class="button is-primary is-fullwidth">
                                        <span class="icon"><i class="fas fa-save"></i></span>
                                        <span>Mettre à jour</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="column is-12">
                <div class="notification is-warning">
                    <strong>Aucune période configurée pour l'année <?= $academicYear ?? $current_academic_year ?? '2024-2025' ?></strong>
                    <p>Utilisez le formulaire ci-dessus pour créer une nouvelle année académique.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Calendrier académique -->
    <?php if (!empty($periodStats)): ?>
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="fas fa-calendar"></i></span>
                Calendrier Académique - <?= $academicYear ?? $current_academic_year ?? '2024-2025' ?>
            </p>
        </header>
        <div class="card-content">
            <div class="table-container">
                <table class="table is-fullwidth is-striped">
                    <thead>
                        <tr>
                            <th>Période</th>
                            <th>Date de début</th>
                            <th>Date de fin</th>
                            <th>Durée</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($periodStats as $periodType => $stats): ?>
                        <tr>
                            <td><strong><?= $stats['name'] ?></strong></td>
                            <td><?= date('d/m/Y', strtotime($stats['start_date'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($stats['end_date'])) ?></td>
                            <td><?= $stats['duration'] ?></td>
                            <td>
                                <?php
                                $statusClass = '';
                                $statusText = $stats['status'];
                                switch ($stats['status']) {
                                    case 'En cours':
                                        $statusClass = 'is-success';
                                        break;
                                    case 'Terminé':
                                        $statusClass = 'is-danger';
                                        break;
                                    case 'À venir':
                                        $statusClass = 'is-warning';
                                        break;
                                    default:
                                        $statusClass = 'is-light';
                                }
                                ?>
                                <span class="tag <?= $statusClass ?>"><?= $statusText ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Informations importantes -->
    <div class="notification is-warning is-light">
        <h4 class="title is-5">Informations importantes :</h4>
        <ul>
            <li>Les périodes académiques déterminent l'organisation des examens</li>
            <li>Les bulletins sont générés par période</li>
            <li>Les statistiques peuvent être filtrées par période</li>
            <li>Les notifications d'examens respectent les périodes définies</li>
            <li>Chaque année académique doit être créée manuellement</li>
            <li>Les périodes par défaut sont créées automatiquement lors de la création d'une année</li>
        </ul>
    </div>

    <!-- Messages de succès/erreur -->
    <?php if (session()->getFlashdata('success')): ?>
    <div class="notification is-success">
        <button class="delete"></button>
        <?= session()->getFlashdata('success') ?>
    </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="notification is-danger">
        <button class="delete"></button>
        <?= session()->getFlashdata('error') ?>
    </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')): ?>
    <div class="notification is-danger">
        <button class="delete"></button>
        <ul>
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
            <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>

<script>
// Fermer les notifications
document.addEventListener('DOMContentLoaded', function() {
    var deleteButtons = document.querySelectorAll('.notification .delete');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            this.parentNode.remove();
        });
    });
});
</script>

<?= $this->endSection() ?>







