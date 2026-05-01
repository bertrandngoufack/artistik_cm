<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">
                <span class="icon"><i class="fas fa-user-tie"></i></span>
                Profil Enseignant
            </h1>
        </div>
        <div class="column is-narrow">
            <div class="buttons">
                <a href="<?= base_url('admin/enseignants/edit/' . $teacher['id']) ?>" class="button is-primary">
                    <span class="icon"><i class="fas fa-edit"></i></span>
                    <span>Modifier</span>
                </a>
                <a href="<?= base_url('admin/enseignants') ?>" class="button is-light">
                    <span class="icon"><i class="fas fa-arrow-left"></i></span>
                    <span>Retour</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Informations principales -->
    <div class="columns is-multiline">
        <div class="column is-8">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="fas fa-info-circle"></i></span>
                        Informations Personnelles
                    </p>
                </header>
                <div class="card-content">
                    <div class="columns is-multiline">
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Nom Complet</label>
                                <div class="control">
                                    <p class="has-text-weight-semibold">
                                        <?= esc($teacher['first_name']) ?> <?= esc($teacher['last_name']) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Email</label>
                                <div class="control">
                                    <p><?= esc($teacher['email']) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Téléphone</label>
                                <div class="control">
                                    <p><?= esc($teacher['phone']) ?: 'Non renseigné' ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Date d'embauche</label>
                                <div class="control">
                                    <p><?= $teacher['hire_date'] ? date('d/m/Y', strtotime($teacher['hire_date'])) : 'Non renseignée' ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Spécialisation</label>
                                <div class="control">
                                    <p><?= esc($teacher['specialization']) ?: 'Non renseignée' ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Qualification</label>
                                <div class="control">
                                    <p><?= esc($teacher['qualification']) ?: 'Non renseignée' ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Statut</label>
                                <div class="control">
                                    <span class="tag <?= $teacher['is_active'] ? 'is-success' : 'is-danger' ?>">
                                        <?= $teacher['is_active'] ? 'Actif' : 'Inactif' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Compte utilisateur</label>
                                <div class="control">
                                    <p><?= $teacher['username'] ?: 'Aucun compte associé' ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="column is-4">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="fas fa-chart-bar"></i></span>
                        Statistiques
                    </p>
                </header>
                <div class="card-content">
                    <div class="columns is-multiline">
                        <div class="column is-6">
                            <div class="box has-background-primary has-text-white has-text-centered">
                                <p class="heading has-text-white">Matières</p>
                                <p class="title has-text-white"><?= $teacher_stats['subjects_count'] ?? 0 ?></p>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="box has-background-info has-text-white has-text-centered">
                                <p class="heading has-text-white">Classes</p>
                                <p class="title has-text-white"><?= $teacher_stats['classes_count'] ?? 0 ?></p>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="box has-background-success has-text-white has-text-centered">
                                <p class="heading has-text-white">Élèves</p>
                                <p class="title has-text-white"><?= $teacher_stats['students_count'] ?? 0 ?></p>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="box has-background-warning has-text-white has-text-centered">
                                <p class="heading has-text-white">Responsable</p>
                                <p class="title has-text-white"><?= $teacher_stats['principal_classes_count'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Matières enseignées -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="fas fa-book"></i></span>
                Matières Enseignées
            </p>
            <div class="card-header-icon">
                <a href="<?= base_url('admin/enseignants/subjects/' . $teacher['id']) ?>" class="button is-small is-primary">
                    <span class="icon"><i class="fas fa-plus"></i></span>
                    <span>Gérer les matières</span>
                </a>
            </div>
        </header>
        <div class="card-content">
            <?php if (!empty($teacher_subjects)): ?>
                <div class="table-container">
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Matière</th>
                                <th>Code</th>
                                <th>Classe</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teacher_subjects as $subject): ?>
                            <tr>
                                <td><?= esc($subject['subject_name']) ?></td>
                                <td><?= esc($subject['subject_code']) ?></td>
                                <td><?= esc($subject['class_name']) ?></td>
                                <td>
                                    <span class="tag is-info">Assigné</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="has-text-centered has-text-grey">
                    <p>Aucune matière assignée</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Classes dont il est responsable -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="fas fa-users"></i></span>
                Classes Responsable Principal
            </p>
            <div class="card-header-icon">
                <a href="<?= base_url('admin/enseignants/classes/' . $teacher['id']) ?>" class="button is-small is-primary">
                    <span class="icon"><i class="fas fa-plus"></i></span>
                    <span>Gérer les classes</span>
                </a>
            </div>
        </header>
        <div class="card-content">
            <?php if (!empty($teacher_classes)): ?>
                <div class="table-container">
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Classe</th>
                                <th>Niveau</th>
                                <th>Série</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teacher_classes as $class): ?>
                            <tr>
                                <td><?= esc($class['name']) ?></td>
                                <td><?= esc($class['level_name']) ?></td>
                                <td><?= esc($class['series_name']) ?></td>
                                <td>
                                    <span class="tag is-success">Responsable Principal</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="has-text-centered has-text-grey">
                    <p>Aucune classe assignée comme responsable principal</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>









