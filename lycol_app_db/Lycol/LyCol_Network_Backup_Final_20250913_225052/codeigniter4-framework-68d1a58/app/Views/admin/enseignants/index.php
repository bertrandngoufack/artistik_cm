<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Module Gestion des Enseignants</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/enseignants/create') ?>" class="button is-primary">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>Nouvel Enseignant</span>
            </a>
        </div>
    </div>
</div>

<div class="columns is-multiline">
    <!-- Statistiques -->
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
                    <p class="title"><?= number_format(count($active_teachers)) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Spécialisations</p>
                    <p class="title"><?= number_format(count($specializations)) ?></p>
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

<!-- Enseignants récents -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-users"></i></span>
            Enseignants Récents
        </p>
        <div class="card-header-icon">
            <a href="<?= base_url('admin/enseignants/list') ?>" class="button is-small is-info">
                <span class="icon"><i class="fas fa-list"></i></span>
                <span>Voir Tout</span>
            </a>
        </div>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped">
                <thead>
                    <tr>
                        <th>Nom Complet</th>
                        <th>Spécialisation</th>
                        <th>Qualification</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_teachers)): ?>
                        <?php foreach ($recent_teachers as $teacher): ?>
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
                                <a href="mailto:<?= esc($teacher['email']) ?>">
                                    <?= esc($teacher['email']) ?>
                                </a>
                            </td>
                            <td><?= esc($teacher['phone'] ?? 'Non défini') ?></td>
                            <td>
                                <?php
                                $statusClass = $teacher['is_active'] ? 'is-success' : 'is-danger';
                                $statusText = $teacher['is_active'] ? 'Actif' : 'Inactif';
                                ?>
                                <span class="tag <?= $statusClass ?>"><?= $statusText ?></span>
                            </td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="<?= base_url('admin/enseignants/show/' . $teacher['id']) ?>" class="button is-info">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/enseignants/edit/' . $teacher['id']) ?>" class="button is-warning">
                                        <span class="icon"><i class="fas fa-edit"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/enseignants/subjects/' . $teacher['id']) ?>" class="button is-success">
                                        <span class="icon"><i class="fas fa-book"></i></span>
                                        <span>Matières</span>
                                    </a>
                                    <a href="<?= base_url('admin/etudes/assignments?teacher_id=' . $teacher['id']) ?>" class="button is-primary">
                                        <span class="icon"><i class="fas fa-chalkboard-teacher"></i></span>
                                        <span>Assignations</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="has-text-centered">
                                <p class="has-text-grey">Aucun enseignant enregistré</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
            <?php foreach ($specializations as $spec): ?>
                <?php
                $count = 0;
                foreach ($active_teachers as $teacher) {
                    if ($teacher['specialization'] === $spec) {
                        $count++;
                    }
                }
                if ($count > 0):
                ?>
                <div class="column is-3">
                    <div class="notification is-info is-light">
                        <div class="content">
                            <p class="title is-5"><?= esc($spec) ?></p>
                            <p class="subtitle is-6"><?= $count ?> enseignant<?= $count > 1 ? 's' : '' ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Actions rapides -->
<div class="columns">
    <div class="column">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-tasks"></i></span>
                    Actions Rapides
                </p>
            </header>
            <div class="card-content">
                <div class="buttons">
                    <a href="<?= base_url('admin/enseignants/list') ?>" class="button is-primary">
                        <span class="icon"><i class="fas fa-list"></i></span>
                        <span>Liste Complète</span>
                    </a>
                    <a href="<?= base_url('admin/enseignants/create') ?>" class="button is-success">
                        <span class="icon"><i class="fas fa-plus"></i></span>
                        <span>Nouvel Enseignant</span>
                    </a>
                    <a href="<?= base_url('admin/enseignants/statistics') ?>" class="button is-info">
                        <span class="icon"><i class="fas fa-chart-bar"></i></span>
                        <span>Statistiques</span>
                    </a>
                    <a href="<?= base_url('admin/enseignants/export/csv') ?>" class="button is-warning">
                        <span class="icon"><i class="fas fa-download"></i></span>
                        <span>Exporter CSV</span>
                    </a>
                    <a href="<?= base_url('admin/etudes/assignments') ?>" class="button is-primary">
                        <span class="icon"><i class="fas fa-chalkboard-teacher"></i></span>
                        <span>Voir Assignations</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fonctionnalités principales -->
<div class="columns is-multiline">
    <div class="column is-6">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-book"></i></span>
                    Gestion des Matières
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <p>Assignez des matières aux enseignants selon leurs spécialisations et compétences.</p>
                    <ul>
                        <li>Assignation multiple de matières</li>
                        <li>Gestion des horaires par classe</li>
                        <li>Suivi des charges de travail</li>
                        <li>Répartition équitable</li>
                    </ul>
                    <a href="<?= base_url('admin/enseignants/list') ?>" class="button is-info is-small">
                        <span class="icon"><i class="fas fa-cog"></i></span>
                        <span>Gérer les Matières</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-6">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-chalkboard-teacher"></i></span>
                    Responsabilités de Classe
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <p>Désignez des enseignants comme responsables principaux de classes.</p>
                    <ul>
                        <li>Enseignant principal par classe</li>
                        <li>Gestion des emplois du temps</li>
                        <li>Suivi des élèves</li>
                        <li>Coordination pédagogique</li>
                    </ul>
                    <a href="<?= base_url('admin/etudes/classes') ?>" class="button is-success is-small">
                        <span class="icon"><i class="fas fa-users"></i></span>
                        <span>Gérer les Classes</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Informations système -->
<div class="notification is-info is-light">
    <div class="content">
        <h4 class="title is-4">
            <span class="icon"><i class="fas fa-info-circle"></i></span>
            Fonctionnalités du Module Enseignants
        </h4>
        <ul>
            <li><strong>Gestion complète des profils :</strong> Informations personnelles, spécialisations, qualifications</li>
            <li><strong>Assignation de matières :</strong> Un enseignant peut enseigner plusieurs matières</li>
            <li><strong>Responsabilités de classe :</strong> Enseignant principal par classe</li>
            <li><strong>Statistiques avancées :</strong> Répartition, charges de travail, expérience</li>
            <li><strong>Intégration utilisateur :</strong> Liaison avec les comptes utilisateurs existants</li>
            <li><strong>Export de données :</strong> Rapports CSV pour analyse externe</li>
        </ul>
    </div>
</div>
<?= $this->endSection() ?>




