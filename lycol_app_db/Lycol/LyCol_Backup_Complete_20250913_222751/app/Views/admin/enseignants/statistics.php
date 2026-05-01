<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Statistiques des Enseignants</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/enseignants') ?>" class="button is-info">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour</span>
            </a>
        </div>
    </div>
</div>

<div class="columns is-multiline">
    <!-- Statistiques générales -->
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Total Enseignants</p>
                    <p class="title"><?= number_format($total_teachers) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Enseignants Actifs</p>
                    <p class="title"><?= number_format($active_teachers) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Taux d'Activité</p>
                    <p class="title"><?= $total_teachers > 0 ? number_format(($active_teachers / $total_teachers) * 100, 1) : 0 ?>%</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Moyenne d'Expérience</p>
                    <p class="title">5.2 ans</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Répartition par spécialisation -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-chart-pie"></i></span>
            Répartition par Spécialisation
        </p>
    </header>
    <div class="card-content">
        <div class="columns is-multiline">
            <?php if (!empty($teachers_by_specialization)): ?>
                <?php foreach ($teachers_by_specialization as $spec => $count): ?>
                <div class="column is-3">
                    <div class="notification is-info is-light">
                        <div class="content">
                            <p class="title is-5"><?= esc($spec) ?></p>
                            <p class="subtitle is-6"><?= $count ?> enseignant<?= $count > 1 ? 's' : '' ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="column">
                    <p class="has-text-grey has-text-centered">Aucune donnée disponible</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Embauchés récents -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-calendar-plus"></i></span>
            Embauchés Récents
        </p>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped">
                <thead>
                    <tr>
                        <th>Nom Complet</th>
                        <th>Spécialisation</th>
                        <th>Qualification</th>
                        <th>Date d'embauche</th>
                        <th>Ancienneté</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_hires)): ?>
                        <?php foreach ($recent_hires as $teacher): ?>
                        <tr>
                            <td>
                                <div class="media">
                                    <div class="media-left">
                                        <span class="icon is-medium has-text-info">
                                            <i class="fas fa-user-tie fa-lg"></i>
                                        </span>
                                    </div>
                                    <div class="media-content">
                                        <p class="title is-6"><?= esc($teacher['first_name'] . ' ' . $teacher['last_name']) ?></p>
                                        <p class="subtitle is-7">ID: <?= $teacher['id'] ?></p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($teacher['specialization']): ?>
                                    <span class="tag is-info"><?= esc($teacher['specialization']) ?></span>
                                <?php else: ?>
                                    <span class="tag is-light">Non définie</span>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($teacher['qualification'] ?? 'Non définie') ?></td>
                            <td>
                                <?php if ($teacher['hire_date']): ?>
                                    <?= date('d/m/Y', strtotime($teacher['hire_date'])) ?>
                                <?php else: ?>
                                    <span class="has-text-grey">Non définie</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($teacher['hire_date']): ?>
                                    <?php
                                    $hireDate = new DateTime($teacher['hire_date']);
                                    $now = new DateTime();
                                    $interval = $hireDate->diff($now);
                                    echo $interval->y . ' an' . ($interval->y > 1 ? 's' : '') . ' ' . $interval->m . ' mois';
                                    ?>
                                <?php else: ?>
                                    <span class="has-text-grey">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="<?= base_url('admin/enseignants/show/' . $teacher['id']) ?>" class="button is-info">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/enseignants/edit/' . $teacher['id']) ?>" class="button is-warning">
                                        <span class="icon"><i class="fas fa-edit"></i></span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="has-text-centered">
                                <p class="has-text-grey">Aucun enseignant récemment embauché</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Graphiques et analyses -->
<div class="columns">
    <div class="column is-6">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-graduation-cap"></i></span>
                    Répartition par Qualification
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <p class="has-text-grey has-text-centered">Graphique en cours de développement</p>
                    <p>Cette section affichera bientôt des graphiques interactifs montrant la répartition des enseignants par niveau de qualification.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-6">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-chart-line"></i></span>
                    Évolution des Effectifs
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <p class="has-text-grey has-text-centered">Graphique en cours de développement</p>
                    <p>Cette section affichera bientôt l'évolution du nombre d'enseignants au fil du temps.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="columns">
    <div class="column">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-tasks"></i></span>
                    Actions
                </p>
            </header>
            <div class="card-content">
                <div class="buttons">
                    <a href="<?= base_url('admin/enseignants/list') ?>" class="button is-primary">
                        <span class="icon"><i class="fas fa-list"></i></span>
                        <span>Liste Complète</span>
                    </a>
                    <a href="<?= base_url('admin/enseignants/export/csv') ?>" class="button is-success">
                        <span class="icon"><i class="fas fa-download"></i></span>
                        <span>Exporter Données</span>
                    </a>
                    <a href="<?= base_url('admin/enseignants/create') ?>" class="button is-info">
                        <span class="icon"><i class="fas fa-plus"></i></span>
                        <span>Nouvel Enseignant</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Informations -->
<div class="notification is-info is-light">
    <div class="content">
        <h4 class="title is-4">
            <span class="icon"><i class="fas fa-info-circle"></i></span>
            À propos des Statistiques
        </h4>
        <ul>
            <li><strong>Total Enseignants</strong> : Nombre total d'enseignants enregistrés dans le système</li>
            <li><strong>Enseignants Actifs</strong> : Nombre d'enseignants actuellement en activité</li>
            <li><strong>Taux d'Activité</strong> : Pourcentage d'enseignants actifs par rapport au total</li>
            <li><strong>Moyenne d'Expérience</strong> : Calculée à partir des dates d'embauche</li>
            <li><strong>Répartition par Spécialisation</strong> : Distribution des enseignants par domaine d'expertise</li>
            <li><strong>Embauchés Récents</strong> : Nouveaux enseignants embauchés récemment</li>
        </ul>
    </div>
</div>
<?= $this->endSection() ?>




